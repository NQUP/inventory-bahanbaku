<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permintaan_bahan_bakus', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pemesanan_id')->constrained()->onDelete('cascade'); // hubungan ke pemesanan
            $table->foreignId('bahan_baku_id')->constrained()->onDelete('cascade'); // bahan baku yg diminta
            $table->decimal('jumlah', 10, 2); // jumlah bahan baku yang dibutuhkan
            $table->enum('status', ['menunggu_supplier', 'dikirim', 'selesai'])->default('menunggu_supplier'); // status proses
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_bahan_bakus');
    }
};
