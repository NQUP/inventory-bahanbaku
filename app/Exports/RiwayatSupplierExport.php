<?php

namespace App\Exports;

use App\Models\PermintaanBahanBaku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RiwayatSupplierExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $supplierId;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($supplierId, $dateFrom = null, $dateTo = null)
    {
        $this->supplierId = $supplierId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo ?? $dateFrom;
    }

    public function collection()
    {
        $query = PermintaanBahanBaku::with(['bahanBaku', 'pemesanan.user'])
            ->onlyForSupplier($this->supplierId)
            ->orderBy('updated_at', 'desc');

        if (!empty($this->dateFrom) || !empty($this->dateTo)) {
            try {
                $from = \Carbon\Carbon::parse($this->dateFrom ?? $this->dateTo)->startOfDay();
                $to = \Carbon\Carbon::parse($this->dateTo ?? $this->dateFrom)->endOfDay();
                if ($from->greaterThan($to)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                $query->whereBetween('created_at', [$from, $to]);
            } catch (\Throwable) {
            }
        }

        $riwayat = $query->get();

        return $riwayat->map(function ($item) {
            return [
                'Kode Permintaan' => $item->kode ?? '-',
                'Nama Bahan Baku' => $item->bahanBaku->nama ?? '-',
                'Jumlah'          => $item->jumlah,
                'Satuan'          => $item->bahanBaku->satuan ?? '-',
                'Nama Pemesan'    => $item->pemesanan->user->name ?? '-',
                'Status'          => ucfirst($item->status),
                'Tanggal Selesai' => $item->updated_at->format('d-m-Y'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Kode Permintaan',
            'Nama Bahan Baku',
            'Jumlah',
            'Satuan',
            'Nama Pemesan',
            'Status',
            'Tanggal Selesai',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->collection()->count() + 1; // +1 untuk heading
        $cellRange = 'A1:G' . $totalRows;

        return [
            $cellRange => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color'       => ['argb' => '000000'],
                    ],
                ],
            ],
        ];
    }
}
