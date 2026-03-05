<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BOM extends Model
{
    use HasFactory;

    protected $table = 'boms';

    protected $fillable = [
        'produk_id',
        'kode',
        'keterangan',
    ];

    // Aktifkan timestamps agar created_at & updated_at otomatis diisi
    public $timestamps = true;

    /**
     * Relasi ke produk jadi (Switch Box, dll)
     */
    public function produk()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    /**
     * Relasi ke detail BOM (bahan baku)
     */
    public function details()
    {
        return $this->hasMany(BOMDetail::class, 'bom_id');
    }

    /**
     * Generate kode otomatis saat create
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bom) {
            if (empty($bom->kode)) {
                $prefix = 'BOM';
                $datePart = date('dmy'); // contoh: 240725
                $countToday = self::whereDate('created_at', date('Y-m-d'))->count() + 1;
                $numberPart = str_pad($countToday, 3, '0', STR_PAD_LEFT); // contoh: 001

                $bom->kode = $prefix . '-' . $datePart . '-' . $numberPart; // hasil: BOM-240725-001
            }
        });
    }
}
