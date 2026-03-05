<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\BOM;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BOMSeeder extends Seeder
{
    public function run(): void
    {
        $plastik = BahanBaku::where('nama', 'Bijih Plastik')->first();

        if (!$plastik) {
            $this->command?->error("Bahan baku 'Bijih Plastik' belum tersedia. Jalankan BahanBakuSeeder terlebih dahulu.");
            return;
        }

        $product = Product::firstOrCreate([
            'nama' => 'Switch Box',
        ]);

        $bom = BOM::firstOrCreate(
            ['produk_id' => $product->id],
            ['keterangan' => 'BOM default untuk Switch Box']
        );

        $bom->details()->updateOrCreate(
            ['bahan_baku_id' => $plastik->id],
            ['jumlah_per_produk' => 1.0]
        );

        $this->command?->info('BOMSeeder selesai.');
    }
}
