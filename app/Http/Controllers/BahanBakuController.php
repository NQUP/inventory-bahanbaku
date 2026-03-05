<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BahanBakuController extends Controller
{
    public function index()
    {
        $bahanbakus = BahanBaku::with('supplier')->get();
        return view('bahanbaku.index', compact('bahanbakus'));
    }

    public function create()
    {
        $suppliers = User::role('supplier')->orderBy('name')->get(['id', 'name']);
        return view('bahanbaku.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'satuan' => 'required|string|max:100',
            'supplier_id' => 'required|exists:users,id',
        ]);

        $today = Carbon::now();
        $tanggal = $today->format('d');
        $bulan = $today->format('m');
        $tahun = $today->format('y');
        $prefix = 'HB'; 

        $countToday = BahanBaku::whereDate('created_at', $today->toDateString())->count() + 1;
        $urutan = str_pad($countToday, 3, '0', STR_PAD_LEFT);

        $kode = $prefix . $tanggal . $bulan . $tahun . $urutan;

        $validated['kode'] = $kode;

        BahanBaku::create($validated);

        return redirect()->route('bahanbaku.index')->with('success', 'Bahan baku berhasil ditambahkan dengan kode: ' . $kode);
    }

    public function edit(BahanBaku $bahanbaku)
    {
        $suppliers = User::role('supplier')->orderBy('name')->get(['id', 'name']);
        return view('bahanbaku.edit', compact('bahanbaku', 'suppliers'));
    }

    public function update(Request $request, BahanBaku $bahanbaku)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'satuan' => 'required|string|max:100',
            'supplier_id' => 'required|exists:users,id',
        ]);

        $bahanbaku->update($validated);

        return redirect()->route('bahanbaku.index')->with('success', 'Bahan baku berhasil diupdate.');
    }

    public function destroy(BahanBaku $bahanbaku)
    {
        $bahanbaku->delete();
        return redirect()->route('bahanbaku.index')->with('success', 'Bahan baku berhasil dihapus.');
    }
}
