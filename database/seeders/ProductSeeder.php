<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::firstOrCreate([
            'nama' => 'Switch Box',
        ]);

        $this->command?->info('ProductSeeder selesai.');
    }
}
