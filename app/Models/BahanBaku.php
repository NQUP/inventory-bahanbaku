<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'stok',
        'stok_minimum',
        'satuan',
        'supplier_id',
        'kode', // tambahkan ini jika belum ada di database dan fillable
    ];

    /**
     * Relasi ke supplier yang menyediakan bahan baku.
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Relasi ke detail BOM (Bill of Materials) yang menggunakan bahan baku ini.
     */
    public function bomDetails()
    {
        return $this->hasMany(BOMDetail::class, 'bahan_baku_id');
    }

    /**
     * Relasi ke tabel konversi satuan (misal gram ke kg, pcs ke karung).
     */
    public function konversiSatuan()
    {
        return $this->hasOne(KonversiSatuan::class);
    }

    /**
     * Relasi ke permintaan bahan baku (bukan pemesanan produk jadi).
     */
    public function permintaanBahan()
    {
        return $this->hasMany(PermintaanBahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Relasi ke parameter EOQ dan ROP.
     */
    public function eoqRop()
    {
        return $this->hasOne(EoqRopParameter::class, 'bahan_baku_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bahanBaku) {
            if (empty($bahanBaku->kode)) {
                $prefix = 'HB'; // Kode Bahan Baku (Hype Black)

                // Ambil tanggal masuk sekarang (hari, bulan, tahun 2 digit)
                $datePart = date('dmy'); // contoh: 250725

                // Hitung jumlah bahan baku yang sudah dibuat hari ini untuk nomor urut
                $countToday = BahanBaku::whereDate('created_at', date('Y-m-d'))->count() + 1;

                $numberPart = str_pad($countToday, 3, '0', STR_PAD_LEFT); // contoh 001

                $bahanBaku->kode = $prefix . $datePart . $numberPart; // contoh HB250725001
            }
        });
    }
}
