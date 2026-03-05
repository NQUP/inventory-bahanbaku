<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan seeder Role, Produk, Bahan Baku, BOM
        $this->call([
            RoleSeeder::class,
            ProductSeeder::class,      // ✅ Tambahkan ini
            BahanBakuSeeder::class,
            BOMSeeder::class,
        ]);

        // Hapus semua user lama
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Buat user dan assign role
        $roles = [
            'admin' => 'admin@gmail.com',
            'manager' => 'manager@gmail.com',
            'gudang' => 'gudang@gmail.com',
            'supplier' => 'supplier@gmail.com',
            'pemesan' => 'pemesan@gmail.com',
        ];

        foreach ($roles as $role => $email) {
            $user = User::create([
                'name' => ucfirst($role),
                'email' => $email,
                'password' => Hash::make('12345'),
            ]);
            $user->assignRole($role);
        }

        // Jalankan PemesananSeeder (butuh produk dan user)
        $this->call([
            PemesananSeeder::class,
        ]);
    }
}
