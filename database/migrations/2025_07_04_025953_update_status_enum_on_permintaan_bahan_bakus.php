<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateStatusEnumOnPermintaanBahanBakus extends Migration
{
    public function up()
    {
        // Tambah enum baru dengan raw SQL (karena Laravel belum native support ubah enum)
        DB::statement("ALTER TABLE permintaan_bahan_bakus 
            MODIFY status ENUM('menunggu_supplier','disiapkan','dikirim','selesai','ditolak','disetujui')");
    }

    public function down()
    {
        // Rollback ke enum awal jika diperlukan
        DB::statement("ALTER TABLE permintaan_bahan_bakus 
            MODIFY status ENUM('menunggu_supplier','disiapkan','dikirim','selesai','ditolak')");
    }
}
