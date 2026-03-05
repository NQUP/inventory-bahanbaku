<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pemesanan;
use App\Models\User;
use App\Models\BahanBaku;
use App\Models\BOM;

class PemesananSeeder extends Seeder
{
    public function run()
    {
        $supplier = User::role('supplier')->inRandomOrder()->first();
        $pemesan = User::role('pemesan')->inRandomOrder()->first();
        $bahanBaku = BahanBaku::first();
        $produk = BOM::first(); // ✅ Ambil produk dari BOM

        if (!$supplier || !$pemesan || (!$bahanBaku && !$produk)) {
            $this->command->info('❌ Supplier, Pemesan, Produk (BOM) atau Bahan Baku tidak ditemukan. Seeder dilewati.');
            return;
        }

        $statuses = ['Pending', 'Dikirim', 'Selesai'];

        for ($i = 0; $i < 5; $i++) {
            Pemesanan::create([
                'user_id' => $pemesan->id,
                'supplier_id' => $supplier->id,
                'produk_id' => $produk?->id, // ✅ utamakan produk via BOM
                'bahan_baku_id' => null,     // ❌ kosongkan jika pesan produk
                'jumlah' => rand(10, 100),
                'status' => $statuses[array_rand($statuses)],
                'tipe' => 'Otomatis', // atau 'Manual' jika kamu ingin bedakan
                'tanggal' => now(),
                'created_at' => now()->subDays(rand(0, 10)),
                'updated_at' => now()->subDays(rand(0, 10)),
            ]);
        }

        $this->command->info('✅ Seeder Pemesanan berhasil dijalankan.');
    }
}
