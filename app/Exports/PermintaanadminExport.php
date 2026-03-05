<?php

namespace App\Exports;

use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class PermintaanadminExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Pemesanan::query()->with('produk.produk', 'user');

        // Filter status
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        // Filter tanggal tunggal
        if ($this->request->filled('tanggal')) {
            $query->whereDate('created_at', $this->request->tanggal);
        }

        // Filter rentang tanggal
        if ($this->request->filled('tanggal_awal') && $this->request->filled('tanggal_akhir')) {
            $query->whereBetween('created_at', [
                $this->request->tanggal_awal . ' 00:00:00',
                $this->request->tanggal_akhir . ' 23:59:59'
            ]);
        }

        $data = $query->get();

        return $data->map(function ($item) {
            return [
                $item->kode ?? '-',
                optional($item->produk?->produk)->nama ?? '-',
                $item->jumlah ?? 0,
                $this->translateStatus($item->status),
                optional($item->user)->name ?? '-',
                optional($item->created_at)->format('d-m-Y') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Produk',
            'Jumlah',
            'Status',
            'Pemesan',
            'Tanggal',
        ];
    }

    protected function translateStatus($status)
    {
        return match ($status) {
            'pending'   => 'Menunggu Persetujuan',
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            'selesai'   => 'Selesai',
            default     => ucfirst($status ?? '-'),
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header bold
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);
            },
        ];
    }
}
