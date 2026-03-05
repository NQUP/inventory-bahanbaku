<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up()
    {
        Schema::table('permintaan_bahan_bakus', function (Blueprint $table) {
            $table->unsignedBigInteger('produk_id')->nullable()->after('pemesanan_id');
            $table->foreign('produk_id')->references('id')->on('boms')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('permintaan_bahan_bakus', function (Blueprint $table) {
            $table->dropForeign(['produk_id']);
            $table->dropColumn('produk_id');
        });
    }
};
