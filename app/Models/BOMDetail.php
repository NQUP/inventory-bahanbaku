<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BOMDetail extends Model
{
    protected $table = 'bom_details'; // ✅ Ini wajib ditambahkan
    protected $fillable = ['bom_id', 'bahan_baku_id', 'jumlah_per_produk'];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function bom()
    {
        return $this->belongsTo(BOM::class);
    }
}
