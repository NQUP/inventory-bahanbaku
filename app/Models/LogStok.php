<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogStok extends Model
{
    use HasFactory;

    protected $fillable = [
        'bahan_baku_id',
        'jenis',         // masuk / keluar
        'jumlah',
        'keterangan',
        'user_id',       // siapa yang melakukan perubahan
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
