<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanBahanBaku;
use App\Models\EoqRopParameter;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PermintaanBahanBakuExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ManagerController extends Controller
{
    public function dashboard(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $dashboardQuery = PermintaanBahanBaku::query()
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));

        $totalPermintaan = (clone $dashboardQuery)->menungguPersetujuanManager()
            ->butuhSupplier()
            ->count();

        $statusCounts = [
            PermintaanBahanBaku::STATUS_MENUNGGU_PERSETUJUAN_MANAGER => (clone $dashboardQuery)->where('status', PermintaanBahanBaku::STATUS_MENUNGGU_PERSETUJUAN_MANAGER)->count(),
            PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER => (clone $dashboardQuery)->where('status', PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER)->count(),
            PermintaanBahanBaku::STATUS_DISETUJUI => (clone $dashboardQuery)->where('status', PermintaanBahanBaku::STATUS_DISETUJUI)->count(),
            PermintaanBahanBaku::STATUS_DITOLAK => (clone $dashboardQuery)->where('status', PermintaanBahanBaku::STATUS_DITOLAK)->count(),
        ];

        $permintaans = (clone $dashboardQuery)->with([
                'bahanBaku.eoqRop',
                'pemesanan.user',
                'pemesanan.produk'
            ])
            ->menungguPersetujuanManager()
            ->butuhSupplier()
            ->latest()
            ->take(5)
            ->get();

        $eoqRopList = EoqRopParameter::with('bahanBaku')->get();

        return view('manager.dashboard', compact(
            'totalPermintaan',
            'permintaans',
            'eoqRopList',
            'statusCounts',
            'dateFrom',
            'dateTo'
        ));
    }
    
public function permintaanIndex()
{
    $permintaans = PermintaanBahanBaku::with([
        'bahanBaku.supplier',
        'bahanBaku.eoqRop',
        'pemesanan.user',
        'pemesanan.produk'
    ])
        ->menungguPersetujuanManager()
        ->butuhSupplier()
        ->orderBy('created_at', 'desc')
        ->get();

    return view('manager.permintaan.index', compact('permintaans'));
}
    public function setujui($id)
    {
        $permintaan = PermintaanBahanBaku::with('bahanBaku.eoqRop', 'bahanBaku.supplier', 'pemesanan')->findOrFail($id);
        $bahanBaku = $permintaan->bahanBaku;
        if (!$bahanBaku) {
            return back()->with('error', 'Bahan baku tidak ditemukan.');
        }

        if (empty($bahanBaku->supplier_id)) {
            return back()->with('error', 'Supplier untuk bahan baku ini belum ditentukan. Lengkapi dulu data bahan baku.');
        }

        $eoq = $bahanBaku->eoqRop->eoq ?? '-';
        $rop = $bahanBaku->eoqRop->rop ?? '-';

        $permintaan->status = PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER;
        $permintaan->save();

        if ($permintaan->pemesanan) {
            $permintaan->pemesanan->status_admin = 'disetujui';
            $permintaan->pemesanan->save();
        }

        return back()->with('success', "Permintaan disetujui. EOQ: $eoq, ROP: $rop");
    }
    public function tolak($id)
    {
        $permintaan = PermintaanBahanBaku::findOrFail($id);
        $permintaan->status = PermintaanBahanBaku::STATUS_DITOLAK;
        $permintaan->save();

        return back()->with('success', 'Permintaan berhasil ditolak.');
    }

    public function riwayat(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $permintaans = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.produk', 'pemesanan.user'])
            ->whereIn('status', PermintaanBahanBaku::STATUSES_MANAGER_REVIEW)
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->latest()
            ->get();

        return view('manager.permintaan.riwayat', compact('permintaans', 'dateFrom', 'dateTo'));
    }

    public function exportRiwayatPdf(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $permintaans = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.produk', 'pemesanan.user'])
            ->whereIn('status', PermintaanBahanBaku::STATUSES_MANAGER_REVIEW)
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.permintaan_pdf', [
            'permintaans' => $permintaans,
            'role' => 'manager',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ])
            ->setPaper('a4', 'landscape');

        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';

        return $pdf->download('permintaan_manager-' . $suffix . '.pdf');
    }

    public function exportRiwayatExcel(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $user = Auth::user();
        $role = 'manager';

        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';

        return Excel::download(
            new PermintaanBahanBakuExport($role, $user->id, $dateFrom?->toDateString(), $dateTo?->toDateString()),
            'laporan_permintaan_manager-' . $suffix . '.xlsx'
        );
    }
}
