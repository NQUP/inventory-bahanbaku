<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Pemesanan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $role = $user->role;

        if (in_array($role, ['admin', 'manager', 'gudang'])) {
            $totalBahanBaku = BahanBaku::count();
            $totalPemesanan = Pemesanan::whereIn('status', ['pending', 'diterima', 'dikirim'])->count();
            $pemesananPending = Pemesanan::where('status', 'pending')->count();
            $pemesananDiterima = Pemesanan::where('status', 'diterima')->count();

            $stokMenipis = BahanBaku::whereColumn('stok', '<=', 'stok_minimum')->get();

            $labels = [];
            $totals = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = date('F', mktime(0, 0, 0, $m, 1));
                $totals[] = Pemesanan::whereYear('created_at', now()->year)
                    ->whereMonth('created_at', $m)
                    ->count();
            }

            return view('dashboard', compact(
                'totalBahanBaku',
                'totalPemesanan',
                'pemesananPending',
                'pemesananDiterima',
                'stokMenipis',
                'labels',
                'totals'
            ));
        } elseif ($role === 'supplier') {
            $jumlahPermintaan = Pemesanan::where('supplier_id', $user->id)->count();
            $permintaanDikonfirmasi = Pemesanan::where('supplier_id', $user->id)
                ->whereIn('status', ['diterima', 'dikirim'])
                ->count();

            return view('dashboard', compact(
                'jumlahPermintaan',
                'permintaanDikonfirmasi'
            ));
        } elseif ($role === 'pemesan') {
            $jumlahPemesananPemesan = Pemesanan::where('user_id', $user->id)->count();
            $statusPemesananTerbaru = Pemesanan::where('user_id', $user->id)
                ->latest('created_at')
                ->value('status');

            return view('dashboard', compact(
                'jumlahPemesananPemesan',
                'statusPemesananTerbaru'
            ));
        } else {
            return view('dashboard');
        }
    }
}
