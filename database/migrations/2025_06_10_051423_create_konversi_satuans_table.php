<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKonversiSatuansTable extends Migration
{
    public function up()
    {
        Schema::create('konversi_satuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            $table->string('satuan_pembelian');
            $table->double('isi_per_satuan');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('konversi_satuans');
    }
}
