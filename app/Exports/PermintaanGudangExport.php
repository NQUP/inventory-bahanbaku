<?php

namespace App\Exports;

use App\Models\PermintaanBahanBaku;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PermintaanGudangExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null
    ) {
        if (!$this->dateTo) {
            $this->dateTo = $this->dateFrom;
        }
    }

    public function collection()
    {
        $query = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->whereIn('status', [
                PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER,
                PermintaanBahanBaku::STATUS_DISIAPKAN,
                PermintaanBahanBaku::STATUS_DIKIRIM,
            ])
            ->latest('updated_at');

        if (!empty($this->dateFrom) || !empty($this->dateTo)) {
            try {
                $from = Carbon::parse($this->dateFrom ?? $this->dateTo)->startOfDay();
                $to = Carbon::parse($this->dateTo ?? $this->dateFrom)->endOfDay();
                if ($from->greaterThan($to)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                $query->whereBetween('created_at', [$from, $to]);
            } catch (\Throwable) {
            }
        }

        return $query->get();
    }

    public function map($item): array
    {
        return [
            $item->updated_at?->format('d-m-Y') ?? '-',
            $item->kode ?? '-',
            $item->bahanBaku->nama ?? '-',
            (int) $item->jumlah,
            $item->bahanBaku->satuan ?? '-',
            $item->pemesanan?->user?->name ?? '-',
            ucfirst(str_replace('_', ' ', $item->status)),
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode',
            'Nama Bahan Baku',
            'Jumlah',
            'Satuan',
            'Nama Pemesan',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A:G')->getAlignment()->setHorizontal('left');

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:G$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        return [];
    }
}
