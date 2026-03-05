<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode', // tambahkan ini
    ];

    public function bom()
    {
        return $this->hasOne(BOM::class, 'produk_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->kode)) {
                $prefix = 'SB'; // Switch Box
                $datePart = date('dmy'); // contoh: 240725

                // Ambil kode terakhir hari ini
                $lastProduct = Product::where('kode', 'like', $prefix . $datePart . '%')
                    ->orderBy('kode', 'desc')
                    ->first();

                $lastNumber = 0;
                if ($lastProduct && preg_match('/^' . $prefix . $datePart . '(\d{3})$/', $lastProduct->kode, $matches)) {
                    $lastNumber = (int) $matches[1];
                }

                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT); // contoh: 002
                $product->kode = $prefix . $datePart . $newNumber; // hasil: SB240725002
            }
        });
    }
}
