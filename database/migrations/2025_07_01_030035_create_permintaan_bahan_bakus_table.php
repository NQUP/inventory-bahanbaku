<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermintaanBahanBakusTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permintaan_bahan_bakus')) {
            Schema::create('permintaan_bahan_bakus', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bahan_baku_id');
                $table->double('jumlah');
                $table->enum('status', ['menunggu', 'menunggu_supplier', 'disetujui', 'ditolak'])->default('menunggu');
                $table->unsignedBigInteger('dibuat_oleh')->nullable();
                $table->timestamps();

                $table->foreign('bahan_baku_id')->references('id')->on('bahan_bakus')->onDelete('cascade');
                $table->foreign('dibuat_oleh')->references('id')->on('users')->onDelete('cascade');
            });

            return;
        }

        // Tabel sudah ada dari migration sebelumnya, hanya lengkapi kolom yang belum ada.
        if (!Schema::hasColumn('permintaan_bahan_bakus', 'dibuat_oleh')) {
            Schema::table('permintaan_bahan_bakus', function (Blueprint $table) {
                $table->unsignedBigInteger('dibuat_oleh')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_bahan_bakus');
    }
}
