<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permintaan_bahan_bakus', function (Blueprint $table) {
            // Ganti nama kolom lama jika ada
            if (Schema::hasColumn('permintaan_bahan_bakus', 'jumlah_dibutuhkan')) {
                $table->renameColumn('jumlah_dibutuhkan', 'jumlah');
            }

            // Tambah kolom pemesanan_id jika belum ada
            if (!Schema::hasColumn('permintaan_bahan_bakus', 'pemesanan_id')) {
                $table->foreignId('pemesanan_id')->nullable()->constrained('pemesanans')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_bahan_bakus', function (Blueprint $table) {
            if (Schema::hasColumn('permintaan_bahan_bakus', 'jumlah')) {
                $table->renameColumn('jumlah', 'jumlah_dibutuhkan');
            }

            if (Schema::hasColumn('permintaan_bahan_bakus', 'pemesanan_id')) {
                $table->dropForeign(['pemesanan_id']);
                $table->dropColumn('pemesanan_id');
            }
        });
    }
};
