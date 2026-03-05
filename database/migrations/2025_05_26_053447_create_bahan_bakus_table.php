<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // database/migrations/xxxx_xx_xx_create_bahan_baku_table.php
        Schema::create('bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('satuan');
            $table->integer('stok');
            $table->integer('stok_minimum');
            $table->decimal('harga', 15, 2);
            $table->unsignedBigInteger('supplier_id')->nullable(); // buat nullable kalau supplier belom ada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_bakus');
    }
};
