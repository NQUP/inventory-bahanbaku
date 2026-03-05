<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('pemesanans', 'status_admin')) {
            Schema::table('pemesanans', function (Blueprint $table) {
                $table->string('status_admin')->default('pending')->after('jumlah');
            });
        }
    }

    public function down()
    {
        Schema::table('pemesanans', function (Blueprint $table) {
            $table->dropColumn('status_admin');
        });
    }
};
