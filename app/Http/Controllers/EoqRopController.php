<?php

namespace App\Http\Controllers;

use App\Models\EoqRopParameter;
use App\Models\BahanBaku;
use Illuminate\Http\Request;

class EoqRopController extends Controller
{
    public function create($bahanBakuId)
    {
        $bahanBaku = BahanBaku::findOrFail($bahanBakuId);
        $parameter = EoqRopParameter::where('bahan_baku_id', $bahanBakuId)->first();

        return view('eoq-rop.create', compact('bahanBaku', 'parameter'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'demand_tahunan' => 'required|numeric|min:1',
            'biaya_pemesanan' => 'required|numeric|min:0',
            'biaya_penyimpanan' => 'required|numeric|min:0.01',
            'lead_time' => 'required|numeric|min:0',
            'safety_stock' => 'nullable|numeric|min:0',
        ]);

        $D = $request->demand_tahunan;
        $S = $request->biaya_pemesanan;
        $H = $request->biaya_penyimpanan;
        $L = $request->lead_time;
        $safetyStock = (float) ($request->input('safety_stock', 0));

        $eoq = round(sqrt((2 * $D * $S) / $H), 2);
        $rop = round((($D / 365) * $L) + $safetyStock, 2);

        EoqRopParameter::updateOrCreate(
            ['bahan_baku_id' => $request->bahan_baku_id],
            [
                'demand_tahunan' => $D,
                'biaya_pemesanan' => $S,
                'biaya_penyimpanan' => $H,
                'lead_time' => $L,
                'eoq' => $eoq,
                'rop' => $rop,
            ]
        );

        if (auth()->user()->hasRole('admin')) {
            return redirect()
                ->route('admin.eoq-rop.create', ['id' => $request->bahan_baku_id])
                ->with('success', 'Parameter EOQ & ROP berhasil disimpan.');
        }

        if (auth()->user()->hasRole('manager')) {
            return redirect()
                ->route('manager.eoq-rop.create', ['id' => $request->bahan_baku_id])
                ->with('success', 'Parameter EOQ & ROP berhasil disimpan.');
        }

        return back()->with('success', 'Parameter EOQ & ROP berhasil disimpan.');
    }
}
