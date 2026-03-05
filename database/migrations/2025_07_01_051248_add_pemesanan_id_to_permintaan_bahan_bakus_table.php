<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('permintaan_bahan_bakus', 'pemesanan_id')) {
            return;
        }

        Schema::table('permintaan_bahan_bakus', function (Blueprint $table) {
            $table->unsignedBigInteger('pemesanan_id')->after('id')->nullable();
            $table->foreign('pemesanan_id')->references('id')->on('pemesanans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('permintaan_bahan_bakus', 'pemesanan_id')) {
            return;
        }

        Schema::table('permintaan_bahan_bakus', function (Blueprint $table) {
            $table->dropForeign(['pemesanan_id']);
            $table->dropColumn('pemesanan_id');
        });
    }
};
