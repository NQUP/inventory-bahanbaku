<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanBahanBaku;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PermintaanSupplierExport;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierController extends Controller
{
    public function dashboard(Request $request)
    {
        $supplierId = auth()->id();
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $permintaansQuery = PermintaanBahanBaku::with(['pemesanan.user', 'bahanBaku'])
            ->onlyForSupplier($supplierId)
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));

        $total = (clone $permintaansQuery)->count();
        $menunggu = (clone $permintaansQuery)->where('status', PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER)->count();
        $dikirim = (clone $permintaansQuery)->where('status', PermintaanBahanBaku::STATUS_DIKIRIM)->count();
        $selesai = (clone $permintaansQuery)->where('status', PermintaanBahanBaku::STATUS_SELESAI)->count();

        $permintaans = $permintaansQuery->latest()->get();

        return view('supplier.dashboard', compact(
            'total',
            'menunggu',
            'dikirim',
            'selesai',
            'permintaans',
            'dateFrom',
            'dateTo'
        ));
    }

    public function kirim($id)
    {
        $supplierId = Auth::id();

        $permintaan = PermintaanBahanBaku::with('bahanBaku')
            ->where('id', $id)
            ->whereHas('bahanBaku', fn($q) => $q->where('supplier_id', $supplierId))
            ->firstOrFail();

        if ($permintaan->status !== PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER) {
            return redirect()->route('supplier.dashboard')->with('error', 'Permintaan tidak bisa dikirim.');
        }

        $permintaan->update(['status' => PermintaanBahanBaku::STATUS_DIKIRIM]);
        return redirect()->route('supplier.dashboard')->with('success', 'Permintaan berhasil dikirim.');
    }

    public function selesai($id)
    {
        $supplierId = Auth::id();

        $permintaan = PermintaanBahanBaku::with('bahanBaku')
            ->where('id', $id)
            ->whereHas('bahanBaku', fn($q) => $q->where('supplier_id', $supplierId))
            ->firstOrFail();

        if ($permintaan->status !== PermintaanBahanBaku::STATUS_DIKIRIM) {
            return redirect()->route('supplier.dashboard')->with('error', 'Permintaan tidak dapat ditandai selesai.');
        }

        $permintaan->update(['status' => PermintaanBahanBaku::STATUS_SELESAI]);
        return redirect()->route('supplier.dashboard')->with('success', 'Permintaan berhasil diselesaikan.');
    }

    public function hapus($id)
    {
        $supplierId = Auth::id();

        $permintaan = PermintaanBahanBaku::with('bahanBaku')
            ->where('id', $id)
            ->whereHas('bahanBaku', fn($q) => $q->where('supplier_id', $supplierId))
            ->firstOrFail();

        if ($permintaan->status !== PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER) {
            return redirect()->route('supplier.dashboard')->with('error', 'Permintaan tidak dapat dihapus.');
        }

        $permintaan->delete();
        return redirect()->route('supplier.dashboard')->with('success', 'Permintaan berhasil dihapus.');
    }

    public function show($id)
    {
        $supplierId = Auth::id();

        $permintaan = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->where('id', $id)
            ->whereHas('bahanBaku', fn($q) => $q->where('supplier_id', $supplierId))
            ->firstOrFail();

        return view('supplier.show', compact('permintaan'));
    }

    public function exportExcel()
    {
        $supplierId = auth()->id();
        return Excel::download(new PermintaanSupplierExport($supplierId), 'permintaan_supplier.xlsx');
    }

    public function exportPdf()
    {
        $supplierId = auth()->id();

        $permintaans = PermintaanBahanBaku::with(['pemesanan.user', 'bahanBaku'])
            ->onlyForSupplier($supplierId)
            ->get();

        $pdf = Pdf::loadView('supplier.exports.pdf', compact('permintaans'))->setPaper('a4', 'landscape');
        return $pdf->download('permintaan_supplier.pdf');
    }

    public function riwayat(Request $request)
    {
        $supplierId = auth()->id();
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $riwayat = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->onlyForSupplier($supplierId)
            ->whereIn('status', [
                PermintaanBahanBaku::STATUS_SELESAI,
                PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER,
            ])
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('supplier.pemesanan.riwayat', compact('riwayat', 'dateFrom', 'dateTo'));
    }

    public function exportRiwayatExcel(Request $request)
    {
        $supplierId = auth()->id();
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';

        return Excel::download(
            new \App\Exports\RiwayatSupplierExport($supplierId, $dateFrom?->toDateString(), $dateTo?->toDateString()),
            'riwayat_supplier-' . $suffix . '.xlsx'
        );
    }

    public function exportRiwayatPdf(Request $request)
    {
        $supplierId = auth()->id();
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $riwayat = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->onlyForSupplier($supplierId)
            ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->orderBy('updated_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('supplier.pemesanan.riwayat_pdf', compact('riwayat', 'dateFrom', 'dateTo'))
                  ->setPaper('a4', 'landscape');

        $suffix = $dateFrom && $dateTo
            ? $dateFrom->toDateString() . '_sd_' . $dateTo->toDateString()
            : 'semua';

        return $pdf->download('riwayat_permintaan_supplier-' . $suffix . '.pdf');
    }
}
