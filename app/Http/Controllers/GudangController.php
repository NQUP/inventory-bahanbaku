<?php

namespace App\Http\Controllers;

use App\Models\PermintaanBahanBaku;
use App\Models\LogStok;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Exports\RiwayatPermintaanGudangExport;
use App\Exports\PermintaanGudangExport;
use Maatwebsite\Excel\Facades\Excel;

class GudangController extends Controller
{
   public function dashboard(Request $request)
{
    [$dateFrom, $dateTo] = $this->resolveDateRange($request);

    $dashboardQuery = PermintaanBahanBaku::query()
        ->whereIn('status', PermintaanBahanBaku::STATUSES_GUDANG)
        ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));

    $totalPermintaan = (clone $dashboardQuery)->count();

    $menunggu = (clone $dashboardQuery)->where('status', PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER)->count();
    $ditolak  = (clone $dashboardQuery)->where('status', PermintaanBahanBaku::STATUS_DITOLAK)->count();
    $dikirim  = (clone $dashboardQuery)->where('status', PermintaanBahanBaku::STATUS_DIKIRIM)->count();
    $selesai  = (clone $dashboardQuery)->where('status', PermintaanBahanBaku::STATUS_SELESAI)->count();

    $permintaansTerbaru = (clone $dashboardQuery)->with(['bahanBaku', 'pemesanan.user'])
        ->orderBy('updated_at', 'desc')
        ->take(5)
        ->get();

    $permintaanSelesai = (clone $dashboardQuery)->with('bahanBaku')
        ->where('status', PermintaanBahanBaku::STATUS_SELESAI)
        ->orderBy('updated_at', 'desc')
        ->take(5)
        ->get();

    return view('gudang.dashboard', compact(
        'totalPermintaan',
        'menunggu',
        'ditolak',
        'dikirim',
        'selesai',
        'permintaansTerbaru',
        'permintaanSelesai',
        'dateFrom',
        'dateTo'
    ));
}


    public function permintaanIndex()
    {
        $permintaans = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->whereIn('status', [
                PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER,
                PermintaanBahanBaku::STATUS_DISIAPKAN,
                PermintaanBahanBaku::STATUS_DIKIRIM,
            ])
            ->latest()
            ->get();

        return view('gudang.permintaan.index', compact('permintaans'));
    }

    public function siapkan($id)
    {
        $result = DB::transaction(function () use ($id) {
            $permintaan = PermintaanBahanBaku::query()->lockForUpdate()->findOrFail($id);

            if ($permintaan->status !== PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER) {
                return ['type' => 'error', 'message' => 'Status permintaan sudah berubah. Muat ulang halaman.'];
            }

            $bahan = BahanBaku::query()->lockForUpdate()->find($permintaan->bahan_baku_id);
            if (!$bahan) {
                return ['type' => 'error', 'message' => 'Data bahan baku tidak ditemukan.'];
            }

            $jumlah = (int) $permintaan->jumlah;
            $stok = (int) ($bahan->stok ?? 0);

            if ($stok < $jumlah) {
                return ['type' => 'info', 'message' => 'Stok tidak mencukupi. Permintaan dialihkan ke supplier (JIT).'];
            }

            $permintaan->status = PermintaanBahanBaku::STATUS_DISIAPKAN;
            $permintaan->save();

            $bahan->stok = $stok - $jumlah;
            $bahan->save();

            LogStok::create([
                'bahan_baku_id' => $bahan->id,
                'jenis'         => 'keluar',
                'jumlah'        => $jumlah,
                'keterangan'    => 'Pengeluaran untuk permintaan ID #' . $permintaan->id,
            ]);

            return ['type' => 'success', 'message' => 'Permintaan berhasil disiapkan dan stok dikurangi.'];
        });

        return back()->with($result['type'], $result['message']);
    }

    public function kirim($id)
    {
        $permintaan = PermintaanBahanBaku::findOrFail($id);

        if ($permintaan->status !== PermintaanBahanBaku::STATUS_DISIAPKAN) {
            return back()->with('error', 'Permintaan belum disiapkan. Tidak bisa dikirim.');
        }

        $permintaan->status = PermintaanBahanBaku::STATUS_DIKIRIM;
        $permintaan->save();

        return back()->with('success', 'Permintaan berhasil dikirim ke pemesan.');
    }

    public function terima($id)
    {
        $result = DB::transaction(function () use ($id) {
            $permintaan = PermintaanBahanBaku::with('pemesanan')->lockForUpdate()->findOrFail($id);

            if ($permintaan->status !== PermintaanBahanBaku::STATUS_DIKIRIM) {
                return ['type' => 'error', 'message' => 'Permintaan belum dikirim oleh supplier.'];
            }

            $bahan = BahanBaku::query()->lockForUpdate()->find($permintaan->bahan_baku_id);
            if (!$bahan) {
                return ['type' => 'error', 'message' => 'Data bahan baku tidak ditemukan.'];
            }

            $bahan->stok = (int) ($bahan->stok ?? 0) + (int) $permintaan->jumlah;
            $bahan->save();

            $permintaan->status = PermintaanBahanBaku::STATUS_SELESAI;
            $permintaan->save();

            LogStok::create([
                'bahan_baku_id' => $bahan->id,
                'jenis'         => 'masuk',
                'jumlah'        => $permintaan->jumlah,
                'keterangan'    => 'Penerimaan bahan dari supplier untuk permintaan #' . $permintaan->id,
            ]);

            $semuaSelesai = PermintaanBahanBaku::where('pemesanan_id', $permintaan->pemesanan_id)
                ->where('status', '!=', PermintaanBahanBaku::STATUS_SELESAI)
                ->count() === 0;

            if ($semuaSelesai && $permintaan->pemesanan) {
                $permintaan->pemesanan->status = 'Selesai';
                $permintaan->pemesanan->save();
            }

            return ['type' => 'success', 'message' => 'Bahan diterima dan stok berhasil ditambahkan.'];
        });

        return back()->with($result['type'], $result['message']);
    }

    public function exportPdf(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $permintaans = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->whereIn('status', [
                PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER,
                PermintaanBahanBaku::STATUS_DISIAPKAN,
                PermintaanBahanBaku::STATUS_DIKIRIM,
            ])
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->latest()
            ->get();

        $pdf = PDF::loadView('gudang.permintaan.pdf', compact('permintaans', 'dateFrom', 'dateTo'));
        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';
        return $pdf->download('permintaan_bahan_baku_gudang-' . $suffix . '.pdf');
    }

    public function riwayatPermintaan(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $permintaans = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->whereIn('status', [
                PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER,
                PermintaanBahanBaku::STATUS_DISIAPKAN,
                PermintaanBahanBaku::STATUS_DIKIRIM,
                PermintaanBahanBaku::STATUS_SELESAI,
            ])
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('gudang.permintaan.riwayat-permintaan', compact('permintaans', 'dateFrom', 'dateTo'));
    }

    public function exportRiwayatPdf(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $permintaans = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->whereIn('status', [
                PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER,
                PermintaanBahanBaku::STATUS_DISIAPKAN,
                PermintaanBahanBaku::STATUS_DIKIRIM,
                PermintaanBahanBaku::STATUS_SELESAI,
            ])
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->orderBy('updated_at', 'desc')
            ->get();

        $pdf = PDF::loadView('gudang.permintaan.riwayat_pdf', compact('permintaans', 'dateFrom', 'dateTo'));
        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';
        return $pdf->download('riwayat_permintaan_gudang-' . $suffix . '.pdf');
    }

    // Export Riwayat Excel dengan filter tanggal
    public function exportRiwayatExcel(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';

        return Excel::download(
            new RiwayatPermintaanGudangExport($dateFrom?->toDateString(), $dateTo?->toDateString()),
            'riwayat_permintaan_gudang-' . $suffix . '.xlsx'
        );
    }

    public function exportExcel(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';

        return Excel::download(
            new PermintaanGudangExport($dateFrom?->toDateString(), $dateTo?->toDateString()),
            'permintaan_bahan_baku_gudang-' . $suffix . '.xlsx'
        );
    }
}
