<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanBaku;
use App\Models\Pemesanan;
use App\Models\Product;
use App\Models\PermintaanBahanBaku;
use App\Models\EoqRopParameter;
use App\Data\AdminDashboardData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PermintaanadminExport;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $pemesananBaseQuery = Pemesanan::whereNotNull('produk_id')
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));

        $permintaanBaseQuery = PermintaanBahanBaku::query()
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));

        $labels = [];
        $dataPemakaian = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M');
            $dataPemakaian[] = (clone $pemesananBaseQuery)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('jumlah');
        }

        $pesananTerbaru = (clone $pemesananBaseQuery)->with(['user', 'produk'])
            ->latest()
            ->take(5)
            ->get();

        $semuaBahan = BahanBaku::with('eoqRop')->orderBy('nama')->get();

        $permintaanTerbaru = (clone $permintaanBaseQuery)->with(['bahanBaku', 'produk', 'pembuat'])
            ->latest()
            ->take(5)
            ->get();

        $data = new AdminDashboardData(
            totalBahanBaku: BahanBaku::count(),
            totalProduk: Product::count(),
            bahanMenipis: BahanBaku::whereColumn('stok', '<', 'stok_minimum')->get(),
            semuaBahan: $semuaBahan,
            totalPemesanan: (clone $pemesananBaseQuery)->count(),
            pemesananBelumSelesai: (clone $pemesananBaseQuery)->where('status_admin', 'pending')->count(),
            grafikPemakaian: [
                'labels' => $labels,
                'data'   => $dataPemakaian,
            ],
            pesananTerbaru: $pesananTerbaru,
            permintaanTerbaru: $permintaanTerbaru
        );

        return view('admin.dashboard', [
            'data' => $data,
            'dateFrom' => $dateFrom?->toDateString(),
            'dateTo' => $dateTo?->toDateString(),
        ]);
    }

    public function setujui($id)
    {
        Log::info("📩 Setujui dipanggil untuk ID: $id");
        DB::beginTransaction();

        try {
            $pemesanan = Pemesanan::with(['produk.details'])->findOrFail($id);
            Log::info("📦 Ditemukan pemesanan: ", $pemesanan->toArray());

            if ($pemesanan->status_admin === 'disetujui') {
                return back()->with('info', 'Pesanan ini sudah disetujui sebelumnya.');
            }

            if (!$pemesanan->produk || $pemesanan->produk->details->isEmpty()) {
                return back()->with('error', 'Produk atau BOM tidak valid.');
            }

            $stokCukup = true;
            $permintaanList = [];

            foreach ($pemesanan->produk->details as $detail) {
                $jumlahDibutuhkan = ($detail->jumlah_per_produk * $pemesanan->jumlah) / 1000;
                if ($jumlahDibutuhkan <= 0 || $jumlahDibutuhkan > 100000000) continue;

                $bahan = BahanBaku::find($detail->bahan_baku_id);
                if (!$bahan) {
                    DB::rollBack();
                    return back()->with('error', 'Bahan baku tidak ditemukan.');
                }

                Log::info("🔍 Cek stok {$bahan->nama}: Stok = {$bahan->stok}, Butuh = {$jumlahDibutuhkan}");

                $permintaanList[] = compact('bahan', 'jumlahDibutuhkan');

                if ($bahan->stok < $jumlahDibutuhkan) {
                    $stokCukup = false;
                }
            }

            if (!$stokCukup) {
                foreach ($permintaanList as $data) {
                    PermintaanBahanBaku::create([
                        'kode' => PermintaanBahanBaku::generateKode(),
                        'pemesanan_id' => $pemesanan->id,
                        'bahan_baku_id' => $data['bahan']->id,
                        'jumlah' => $data['jumlahDibutuhkan'],
                        'status' => PermintaanBahanBaku::STATUS_MENUNGGU_PERSETUJUAN_MANAGER,
                        'dibuat_oleh' => auth()->id() ?? 1,
                    ]);
                    Log::info("⚠️ Stok kurang: Permintaan dibuat untuk {$data['bahan']->nama}");
                }

                $pemesanan->status_admin = 'menunggu_persetujuan_manager';
                $pemesanan->status = 'diproses';
                $pemesanan->save();

                DB::commit();
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'Stok tidak mencukupi. Permintaan diteruskan ke Manager.');
            }

            foreach ($permintaanList as $data) {
                $bahan = $data['bahan'];
                $jumlah = $data['jumlahDibutuhkan'];

                PermintaanBahanBaku::create([
                    'kode' => PermintaanBahanBaku::generateKode(),
                    'pemesanan_id' => $pemesanan->id,
                    'bahan_baku_id' => $bahan->id,
                    'jumlah' => $jumlah,
                    'status' => PermintaanBahanBaku::STATUS_DISETUJUI,
                    'dibuat_oleh' => auth()->id() ?? 1,
                ]);

                $bahan->stok -= $jumlah;
                $bahan->save();

                $param = EoqRopParameter::firstOrNew(['bahan_baku_id' => $bahan->id]);
                $d = $param->demand_tahunan ?? 0;
                $s = $param->biaya_pemesanan ?? 0;
                $h = $param->biaya_penyimpanan ?? 0;
                $lt = $param->lead_time ?? 0;

                if ($d > 0 && $s > 0 && $h > 0) {
                    $eoq = round(sqrt((2 * $d * $s) / $h), 2);
                    $rop = $param->rop ?? round(($d / 365) * $lt, 2);
                    $param->eoq = $eoq;
                    $param->rop = $rop;
                    $param->save();
                    Log::info("📊 EOQ/ROP untuk {$bahan->nama}: EOQ=$eoq, ROP=$rop");
                }
            }

            $pemesanan->status_admin = 'disetujui';
            $pemesanan->status = 'Selesai';
            $pemesanan->save();

            DB::commit();
            return redirect()->route('admin.dashboard')
                ->with('success', 'Pesanan disetujui dan stok mencukupi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Gagal menyetujui pesanan: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyetujui pesanan.');
        }
    }

    public function tolak($id)
    {
        $pemesanan = Pemesanan::findOrFail($id);
        $pemesanan->status_admin = 'ditolak';
        $pemesanan->save();

        return back()->with('success', 'Pesanan berhasil ditolak.');
    }

    public function exportPdf(Request $request)
    {
        $tanggal = $request->input('tanggal');

        $pemesanans = Pemesanan::with(['produk', 'user'])
            ->when($tanggal, fn($q) => $q->whereDate('created_at', $tanggal))
            ->get();

        $pdf = Pdf::loadView('admin.pemesanan.pdf', compact('pemesanans', 'tanggal'))
                  ->setPaper('A4', 'landscape');

        return $pdf->download('laporan_pemesanan_produk_jadi-'.($tanggal ?? 'semua').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new PermintaanadminExport($request), 'laporan_pemesanan_produk_jadi.xlsx');
    }
}


