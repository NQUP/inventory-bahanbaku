<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PermintaanBahanBaku extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU_PERSETUJUAN_MANAGER = 'menunggu_persetujuan_manager';
    public const STATUS_MENUNGGU_SUPPLIER = 'menunggu_supplier';
    public const STATUS_DISIAPKAN = 'disiapkan';
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    public const STATUSES_GUDANG = [
        self::STATUS_MENUNGGU_SUPPLIER,
        self::STATUS_DISIAPKAN,
        self::STATUS_DIKIRIM,
        self::STATUS_SELESAI,
        self::STATUS_DITOLAK,
    ];

    public const STATUSES_MANAGER_REVIEW = [
        self::STATUS_MENUNGGU_PERSETUJUAN_MANAGER,
        self::STATUS_DISETUJUI,
        self::STATUS_DITOLAK,
        self::STATUS_DIKIRIM,
        self::STATUS_SELESAI,
        self::STATUS_MENUNGGU_SUPPLIER,
    ];

    public const STATUSES_SUPPLIER = [
        self::STATUS_MENUNGGU_SUPPLIER,
        self::STATUS_DIKIRIM,
        self::STATUS_SELESAI,
    ];

    protected $table = 'permintaan_bahan_bakus';

    protected $fillable = [
        'kode',
        'pemesanan_id',
        'produk_id', // ✅ jangan lupa juga tambahkan ke fillable
        'bahan_baku_id',
        'jumlah',
        'status', // menunggu_persetujuan_manager, menunggu_supplier, dll.
        'dibuat_oleh',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * ✅ Relasi ke BOM (produk jadi).
     */
    public function produk()
    {
        return $this->belongsTo(BOM::class, 'produk_id');
    }

    public function butuhSupplier(): bool
    {
        if (!$this->bahanBaku) return false;

        return $this->bahanBaku->stok < $this->jumlah;
    }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    /**
     * Scope untuk mengambil permintaan yang menunggu persetujuan manager.
     */
    public function scopeMenungguPersetujuanManager($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU_PERSETUJUAN_MANAGER);
    }
    public function scopeButuhSupplier($query)
    {
        return $query->whereHas('bahanBaku', function ($q) {
            $q->whereColumn('stok', '<', 'permintaan_bahan_bakus.jumlah');
        });
    }

    public function scopeOnlyForSupplier($query, $supplierId)
    {
        return $query->whereIn('status', self::STATUSES_SUPPLIER)
            ->whereHas('bahanBaku', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
    }
    public function scopeUntukGudang($query)
    {
        return $query->whereIn('status', self::STATUSES_GUDANG);
    }
    public static function generateKode()
    {
        $prefix = 'HB'; // Bisa disesuaikan
        $now = now();
        $tanggal = $now->format('d');
        $bulan = $now->format('m');
        $tahun = $now->format('y');

        // Hitung jumlah permintaan yang dibuat hari ini
        $countToday = self::whereDate('created_at', $now->toDateString())->count();
        $urutan = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $tanggal . $bulan . $tahun . $urutan;
    }
}

