<?php

namespace Database\Seeders;

// database/seeders/DummySeeder.php

use Illuminate\Database\Seeder;
use App\Models\BahanBaku;
use App\Models\Pemesanan;

class DummySeeder extends Seeder
{
    public function run()
    {
        BahanBaku::factory()->count(5)->create(); // pastikan ada factory
        Pemesanan::factory()->count(10)->create(); // juga pastikan ada factory
    }
}
