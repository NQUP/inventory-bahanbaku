<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBaku;

class BahanBakuSeeder extends Seeder
{
    public function run()
    {
        BahanBaku::create([
            'nama' => 'Bijih Plastik',
            'stok' => 20,
            'stok_minimum' => 30,
            'satuan' => 'Kg',
            'harga' => 15000, // Tambahkan ini
        ]);
    }
}
