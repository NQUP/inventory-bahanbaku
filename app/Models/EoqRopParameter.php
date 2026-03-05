<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EoqRopParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'bahan_baku_id',
        'demand_tahunan',
        'biaya_pemesanan',
        'biaya_penyimpanan',
        'lead_time',
        'eoq',
        'rop',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    // Getter opsional untuk membulatkan nilai EOQ dan ROP saat ditampilkan
    public function getEoqRoundedAttribute()
    {
        return round($this->eoq);
    }

    public function getRopRoundedAttribute()
    {
        return round($this->rop);
    }
}
