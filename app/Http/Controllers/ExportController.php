<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Exports\PemesananExport;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PermintaanBahanBaku;
use App\Exports\PermintaanBahanBakuExport;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function exportPDF(Request $request)
    {
        $status = $request->input('status');
        $tanggal = $request->input('tanggal'); 
        $pemesanans = Pemesanan::with(['produk.produk'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($tanggal, fn($q) => $q->whereDate('created_at', Carbon::parse($tanggal)->toDateString()))
            ->latest()
            ->get();

        $pdf = PDF::loadView('exports.pemesanan_pdf', compact('pemesanans', 'tanggal'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan_pemesanan-'.($tanggal ?? 'semua').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $status = $request->input('status');
        $tanggal = $request->input('tanggal');

        return Excel::download(new PemesananExport($status, $tanggal), 'laporan_pemesanan.xlsx');
    }

    public function exportManagerPDF(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $permintaans = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->whereIn('status', PermintaanBahanBaku::STATUSES_MANAGER_REVIEW)
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->latest()
            ->get();

        $pdf = PDF::loadView('exports.permintaan_pdf', [
            'permintaans' => $permintaans,
            'role' => 'manager',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ])->setPaper('a4', 'landscape');

        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';

        return $pdf->download('permintaan_manager-' . $suffix . '.pdf');
    }

    public function exportManagerExcel(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $user = auth()->user();
        $role = 'manager';

        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';

        return Excel::download(
            new PermintaanBahanBakuExport($role, $user->id, $dateFrom?->toDateString(), $dateTo?->toDateString()),
            'permintaan_manager-' . $suffix . '.xlsx'
        );
    }
}
