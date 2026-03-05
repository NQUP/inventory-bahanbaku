<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KebutuhanSupplier extends Model
{
    protected $fillable = ['supplier_id', 'bahan_baku_id', 'jumlah', 'status'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
