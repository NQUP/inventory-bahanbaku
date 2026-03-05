<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{
    use HasFactory;

    protected $fillable = ['bom_id', 'jumlah_produk'];

    // Relasi ke BOM
    public function bom()
    {
        return $this->belongsTo(BOM::class, 'bom_id');
    }

    // Konversi kebutuhan bahan baku secara otomatis
    public function kebutuhanBahanBaku()
    {
        $bahanBaku = [];

        foreach ($this->bom->details as $detail) {
            $totalKebutuhan = $this->jumlah_produk * $detail->jumlah_per_produk;

            $bahanBaku[] = [
                'nama_bahan_baku' => $detail->bahanBaku->nama,
                'jumlah' => $totalKebutuhan,
                'satuan' => $detail->bahanBaku->satuan ?? '-', // jika ada field satuan
            ];
        }

        return $bahanBaku;
    }
}
