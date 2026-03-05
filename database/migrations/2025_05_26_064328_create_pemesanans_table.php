<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->nullable()->constrained('bahan_bakus')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');       // pemesan
            $table->foreignId('supplier_id')->nullable()->constrained('users')->onDelete('set null'); // supplier
            $table->integer('jumlah');
            $table->string('status'); // contoh: Pending, Diterima, Ditolak, Dikirim, Selesai
            $table->string('tipe');   // contoh: Manual, Otomatis
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanans');
    }
};
