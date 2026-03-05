<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonversiSatuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'bahan_baku_id',
        'satuan_pembelian',
        'isi_per_satuan'
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
