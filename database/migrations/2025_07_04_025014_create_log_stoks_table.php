<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogStoksTable extends Migration
{
    public function up(): void
    {
        Schema::create('log_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained()->onDelete('cascade');
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->double('jumlah');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_stoks');
    }
}
