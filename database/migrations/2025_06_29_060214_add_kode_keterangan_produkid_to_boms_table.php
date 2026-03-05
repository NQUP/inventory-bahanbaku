<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Tambah kolom jika belum ada
            $table->string('kode')->after('id');
            $table->string('keterangan')->nullable()->after('kode');
            // Kolom produk_id sengaja tidak ditambah lagi karena sudah ada
        });
    }

    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Jika sebelumnya foreign key sudah dibuat, hapus dengan aman
            if (Schema::hasColumn('boms', 'produk_id')) {
                $table->dropForeign(['produk_id']);
            }

            $table->dropColumn(['kode', 'keterangan']);
            // Jangan drop produk_id jika kamu masih pakai, atau ubah sesuai kebutuhan
        });
    }
};
