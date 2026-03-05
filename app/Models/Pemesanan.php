<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $fillable = [
        'user_id',
        'produk_id',
        'bahan_baku_id',
        'jumlah',
        'status',
        'status_admin',
        'supplier_id',
        'tanggal',
        'tipe',
        'kode', // tambahkan ini
    ];

    /**
     * Relasi ke BOM (produk jadi).
     */
    public function produk()
    {
        return $this->belongsTo(BOM::class, 'produk_id');
    }

    /**
     * Relasi ke user pemesan (Produksi).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke supplier (jika dipesan langsung).
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Relasi ke bahan baku langsung (jika tipe = bahan_baku).
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Relasi ke semua permintaan bahan baku hasil dari pemesanan ini.
     */
    public function permintaanBahanBakus()
    {
        return $this->hasMany(PermintaanBahanBaku::class, 'pemesanan_id');
    }

    /**
     * Format status admin (label UI).
     */
    public function statusAdminLabel(): string
    {
        return ucwords($this->status_admin ?? 'pending');
    }

    /**
     * Cek apakah pemesanan ini adalah tipe produk jadi.
     */
    public function isProduk(): bool
    {
        return $this->tipe === 'produk';
    }

    /**
     * Cek apakah pemesanan ini adalah tipe bahan baku langsung.
     */
    public function isBahanBaku(): bool
    {
        return $this->tipe === 'bahan_baku';
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pemesanan) {
            // Lewati jika sudah ada kode
            if ($pemesanan->kode) return;

            // Ambil tanggal produksi
            $date = now()->format('dmy'); // contoh: 260725

            // Default prefix
            $prefix = 'SB';

            // Gunakan awalan nama produk jika tipe = produk
            if ($pemesanan->isProduk() && $pemesanan->produk && $pemesanan->produk->produk) {
                $namaProduk = strtoupper($pemesanan->produk->produk->nama ?? '');
                $words = explode(' ', $namaProduk);
                $prefix = '';

                foreach ($words as $word) {
                    $prefix .= substr($word, 0, 1);
                    if (strlen($prefix) >= 2) break; // ambil 2 huruf saja
                }

                $prefix = str_pad($prefix, 2, 'X'); // jika hanya 1 kata, misal "Panel" => PX
            }

            // Ganti prefix jika tipe bahan baku langsung
            if ($pemesanan->isBahanBaku()) {
                $prefix = 'BB';
            }

            $kodePrefix = $prefix . $date;

            // Ambil kode terakhir yang dimulai dengan prefix+date
            $lastKode = self::where('kode', 'like', "$kodePrefix%")
                ->orderByDesc('kode')
                ->value('kode');

            $lastNumber = $lastKode ? (int)substr($lastKode, -3) : 0;
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            // Set kode ke model
            $pemesanan->kode = $kodePrefix . $nextNumber;
        });
    }
}
