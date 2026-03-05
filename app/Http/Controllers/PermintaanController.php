<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanBahanBaku;
use Illuminate\Support\Facades\Auth;
use App\Models\Pemesanan;

class PermintaanController extends Controller
{
    public function setujui($id)
    {
        $permintaan = PermintaanBahanBaku::with('bahanBaku')->findOrFail($id);

        $bahan = $permintaan->bahanBaku;
        $jumlah = $permintaan->jumlah;

        if ($bahan->stok >= $jumlah) {
            $permintaan->update([
                'status' => PermintaanBahanBaku::STATUS_DISETUJUI,
            ]);

            $bahan->stok -= $jumlah;
            $bahan->save();

            Pemesanan::create([
                'bahan_baku_id' => $permintaan->bahan_baku_id,
                'jumlah' => $jumlah,
                'status' => 'menunggu_supplier',
                'created_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'Permintaan disetujui, stok cukup, dan pemesanan dikirim ke supplier.');
        } else {
            return redirect()->back()->with('error', 'Stok tidak mencukupi untuk menyetujui permintaan.');
        }
    }

    public function tolak($id)
    {
        $permintaan = PermintaanBahanBaku::findOrFail($id);

        $permintaan->update([
            'status' => PermintaanBahanBaku::STATUS_DITOLAK,
        ]);

        return redirect()->back()->with('success', 'Permintaan ditolak.');
    }
}
