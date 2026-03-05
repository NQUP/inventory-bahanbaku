<?php

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class AdminDashboardData extends Data
{
    public function __construct(
        public int $totalBahanBaku,
        public int $totalProduk,
        public Collection $bahanMenipis,
        public int $totalPemesanan,
        public int $pemesananBelumSelesai,
        public array $grafikPemakaian,
        public Collection $pesananTerbaru,
        public Collection $semuaBahan, // ✅ Tambahan untuk semua bahan
        public $permintaanTerbaru
    ) {}
}
