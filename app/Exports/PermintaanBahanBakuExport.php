<?php

namespace App\Exports;

use App\Models\PermintaanBahanBaku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PermintaanBahanBakuExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $role;
    protected $userId;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($role, $userId, $dateFrom = null, $dateTo = null)
    {
        $this->role = $role;
        $this->userId = $userId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo ?? $dateFrom;
    }

    public function collection()
    {
        $query = PermintaanBahanBaku::with([
            'bahanBaku',
            'pemesanan.produk.produk',
            'pemesanan.user'
        ]);

        // Filter status sesuai role
        if ($this->role === 'gudang') {
            $query->whereIn('status', [
                PermintaanBahanBaku::STATUS_DIKIRIM,
                PermintaanBahanBaku::STATUS_SELESAI,
            ]);
        } elseif ($this->role === 'supplier') {
            $query->whereHas('bahanBaku', fn($q) => $q->where('supplier_id', $this->userId));
        } elseif ($this->role === 'manager') {
            $query->whereIn('status', PermintaanBahanBaku::STATUSES_MANAGER_REVIEW);
        }

        // Filter rentang tanggal jika ada
        if (!empty($this->dateFrom) || !empty($this->dateTo)) {
            try {
                $from = Carbon::parse($this->dateFrom ?? $this->dateTo)->startOfDay();
                $to = Carbon::parse($this->dateTo ?? $this->dateFrom)->endOfDay();
                if ($from->greaterThan($to)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                $query->whereBetween('created_at', [$from, $to]);
            } catch (\Exception $e) {
                \Log::error("Format tanggal salah untuk export permintaan bahan baku.");
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function map($item): array
    {
        static $index = 1;
        return [
            $index++,
            $item->kode,
            $item->pemesanan->produk?->produk?->nama ?? '-',
            $item->bahanBaku->nama ?? '-',
            number_format($item->jumlah, 2, ',', '.'),
            ucfirst(str_replace('_', ' ', $item->status)),
            $item->pemesanan->user->name ?? '-',
            $item->created_at->format('d M Y'),
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Kode',
            'Nama Produk',
            'Nama Bahan Baku',
            'Jumlah',
            'Status',
            'Pemesan',
            'Tanggal',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Ambil jumlah baris otomatis dari sheet
        $totalRows = $sheet->getHighestRow();

        $sheet->getStyle("A1:H{$totalRows}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    }
}
