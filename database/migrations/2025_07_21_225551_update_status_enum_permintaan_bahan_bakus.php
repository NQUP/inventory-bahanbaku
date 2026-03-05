<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE permintaan_bahan_bakus MODIFY COLUMN status ENUM(
            'menunggu_persetujuan_manager',
            'menunggu_supplier',
            'dikirim',
            'selesai',
            'disetujui',
            'ditolak'
        ) DEFAULT 'menunggu_persetujuan_manager'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE permintaan_bahan_bakus MODIFY COLUMN status ENUM(
            'menunggu_supplier',
            'dikirim',
            'selesai',
            'disetujui',
            'ditolak'
        ) DEFAULT 'menunggu_supplier'");
    }
};
