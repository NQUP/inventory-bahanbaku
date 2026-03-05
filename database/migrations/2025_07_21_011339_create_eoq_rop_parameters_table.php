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
        Schema::create('eoq_rop_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            $table->integer('demand_tahunan');
            $table->decimal('biaya_pemesanan', 10, 2);
            $table->decimal('biaya_penyimpanan', 10, 2);
            $table->integer('lead_time'); // dalam hari
            $table->decimal('eoq', 10, 2)->nullable();
            $table->integer('rop')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eoq_rop_parameters');
    }
};
