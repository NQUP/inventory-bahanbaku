<?php

namespace App\Http\Controllers;

use App\Models\BOM;
use App\Models\BOMDetail;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BOMController extends Controller
{
    public function index()
    {
        $boms = BOM::with('details.bahanBaku')->get();
        return view('admin.bom.index', compact('boms'));
    }

    public function create()
    {
        $bahanBakus = BahanBaku::all();
        return view('admin.bom.create', compact('bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string',
            'bahan_baku.*.id' => 'required|exists:bahan_bakus,id',
            'bahan_baku.*.jumlah' => 'required|numeric|min:0.01',
        ]);

        $today = Carbon::now();
        $tanggal = $today->format('d');
        $bulan = $today->format('m');
        $tahun = $today->format('y');
        $prefix = 'SB'; 

        $countToday = BOM::whereDate('created_at', $today->toDateString())->count() + 1;
        $urutan = str_pad($countToday, 3, '0', STR_PAD_LEFT);

        $kode = $prefix . $tanggal . $bulan . $tahun . $urutan;

        $bom = BOM::create([
            'kode' => $kode,
            'nama_produk' => $request->nama_produk,
        ]);

        foreach ($request->bahan_baku as $bahan) {
            BOMDetail::create([
                'bom_id' => $bom->id,
                'bahan_baku_id' => $bahan['id'],
                'jumlah_per_produk' => $bahan['jumlah'],
            ]);
        }

        return redirect()->route('bom.index')->with('success', 'BOM berhasil dibuat dengan kode: ' . $kode);
    }
}
