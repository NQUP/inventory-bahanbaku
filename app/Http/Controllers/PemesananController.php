<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\BOM;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use App\Exports\PemesananExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PemesananController extends Controller
{
    public function dashboard(Request $request)
    {
        $status = $request->input('status');
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $query = Pemesanan::with(['produk.produk', 'produk.details.bahanBaku', 'bahanBaku'])
            ->whereNotNull('produk_id')
            ->where('user_id', Auth::id())
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));

        $total = (clone $query)->sum('jumlah');

        $bahanBakuTotal = $query->get()->reduce(function ($carry, $pemesanan) {
            $jumlahProduk = $pemesanan->jumlah;
            $totalGram = $pemesanan->produk->details->sum(function ($detail) use ($jumlahProduk) {
                return $detail->jumlah_per_produk * $jumlahProduk;
            });
            return $carry + $totalGram / 1000;
        }, 0);

        $belumSelesai = (clone $query)->where('status', '!=', 'Selesai')->count();
        $selesai = (clone $query)->where('status', 'Selesai')->count();

        $pemesanans = (clone $query)->latest()->paginate(10);

        return view('pemesanan.dashboard', compact(
            'total',
            'belumSelesai',
            'selesai',
            'pemesanans',
            'bahanBakuTotal',
            'dateFrom',
            'dateTo',
            'status'
        ));
    }

    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal');

        $query = Pemesanan::with(['user', 'produk'])->latest();

        if ($tanggal) {
            $query->whereDate('created_at', $tanggal);
        }

        $pemesanans = $query->get();

        return view('admin.pemesanan.index', compact('pemesanans', 'tanggal'));
    }

    public function create()
    {
        $boms = BOM::all();
        return view('pemesanan.create', compact('boms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:boms,id',
            'jumlah' => 'required|numeric|min:1',
        ]);

        try {
            $produk = BOM::with('details.bahanBaku')->findOrFail($request->produk_id);
            $jumlahPesanan = $request->jumlah;
            $stokKurang = [];

            foreach ($produk->details as $detail) {
                $bahan = $detail->bahanBaku;
                if (!$bahan) {
                    return back()->with('error', 'Data bahan baku tidak ditemukan.');
                }

                $totalKebutuhan = $detail->jumlah_per_produk * $jumlahPesanan;
                $stokTersedia = $bahan->stok ?? 0;

                if ($stokTersedia < $totalKebutuhan) {
                    $stokKurang[] = $bahan->nama;
                }
            }

            $tanggal = now();
            $payload = [
                'user_id' => Auth::id(),
                'produk_id' => $produk->id,
                'jumlah' => $jumlahPesanan,
                'status' => 'Pending',
                'status_admin' => 'pending',
                'tipe' => 'produk',
                'tanggal' => $tanggal,
                'supplier_id' => null,
            ];

            $pemesanan = null;
            $maxAttempts = 5;
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                try {
                    $pemesanan = Pemesanan::create($payload);
                    break;
                } catch (QueryException $e) {
                    if (!$this->isDuplicateKodeException($e) || $attempt === $maxAttempts) {
                        throw $e;
                    }

                    usleep(random_int(20000, 80000));
                }
            }

            if (!empty($stokKurang)) {
                return redirect()->route('pemesanan.dashboard')
                    ->with('warning', 'Stok kurang untuk: ' . implode(', ', $stokKurang) . '. Menunggu persetujuan Admin.');
            }

            return redirect()->route('pemesanan.dashboard')
                ->with('success', 'Pemesanan berhasil dibuat dengan kode produk: ' . ($pemesanan->kode ?? '-'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan pemesanan: ' . $e->getMessage());
        }
    }

    public function konversiPreview(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:boms,id',
            'jumlah' => 'required|numeric|min:1',
        ]);

        $produk = BOM::with('details.bahanBaku.konversiSatuan')->findOrFail($request->produk_id);
        $jumlahPesanan = $request->jumlah;
        $detailBahan = [];

        foreach ($produk->details as $detail) {
            $bahan = $detail->bahanBaku;
            $kebutuhanTotal = $detail->jumlah_per_produk * $jumlahPesanan;
            $konversi = $bahan->konversiSatuan;

            $jumlahDalamSatuanPembelian = $konversi
                ? $kebutuhanTotal / $konversi->isi_per_satuan
                : null;

            $cukup = $bahan->stok >= $kebutuhanTotal;

            $detailBahan[] = [
                'nama' => $bahan->nama,
                'kebutuhan_total' => $kebutuhanTotal,
                'satuan' => $bahan->satuan,
                'jumlah_dalam_satuan_pembelian' => $jumlahDalamSatuanPembelian ? number_format($jumlahDalamSatuanPembelian, 2) : '-',
                'satuan_pembelian' => $konversi->satuan_pembelian ?? '-',
                'stok_saat_ini' => $bahan->stok,
                'cukup' => $cukup,
            ];
        }

        return view('pemesanan.hasil_konversi', compact('detailBahan', 'produk', 'jumlahPesanan'));
    }

    public function edit($id)
    {
        $pemesanan = Pemesanan::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->firstOrFail();

        $boms = BOM::all();

        return view('pemesanan.edit', compact('pemesanan', 'boms'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'produk_id' => 'required|exists:boms,id',
            'jumlah' => 'required|numeric|min:1',
        ]);

        $pemesanan = Pemesanan::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->firstOrFail();

        $pemesanan->update([
            'produk_id' => $request->produk_id,
            'jumlah' => $request->jumlah,
        ]);

        return redirect()->route('pemesanan.dashboard')->with('success', 'Pemesanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pemesanan = Pemesanan::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->firstOrFail();

        $pemesanan->delete();

        return redirect()->route('pemesanan.dashboard')->with('success', 'Pemesanan berhasil dihapus.');
    }

   public function exportExcel(Request $request)
{
    $status = $request->input('status');   
    $tanggal = $request->input('tanggal'); 

    if ($tanggal) {
        try {
            $tanggal = \Carbon\Carbon::parse($tanggal)->toDateString();
        } catch (\Exception $e) {
            $tanggal = null; 
        }
    }

    return Excel::download(new PemesananExport($status, $tanggal), 'laporan_pemesanan.xlsx');
}

    public function exportPDF(Request $request)
    {
        $tanggal = $request->input('tanggal');

        $query = Pemesanan::with(['produk.produk', 'user'])
            ->when($tanggal, fn($q) => $q->whereDate('created_at', $tanggal))
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.pemesan_pdf', compact('query', 'tanggal'));
        return $pdf->download('laporan_pemesanan.pdf');
    }

    public function generateKode(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:boms,id',
        ]);

        $produkId = $request->produk_id;
        $tanggal = now();
        $tanggalFormat = $tanggal->format('dmy');
        $jumlahHariIni = Pemesanan::whereDate('created_at', $tanggal->toDateString())
            ->where('produk_id', $produkId)
            ->count();

        $kodeAwal = 'SB';
        $nomorUrut = str_pad($jumlahHariIni + 1, 3, '0', STR_PAD_LEFT);
        $kodeProduk = $kodeAwal . $tanggalFormat . $nomorUrut;

        return response()->json(['kode' => $kodeProduk]);
    }

    private function isDuplicateKodeException(QueryException $e): bool
    {
        $message = $e->getMessage();

        return (string) $e->getCode() === '23000'
            && str_contains($message, 'pemesanans_kode_unique');
    }
}
