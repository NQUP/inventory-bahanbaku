<?php

namespace App\Exports;

use App\Models\Pemesanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PemesananExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    protected $status;
    protected $tanggal;

    public function __construct($status = null, $tanggal = null)
    {
        $this->status = $status;
        $this->tanggal = $tanggal;
    }

    public function collection()
    {
        $query = Pemesanan::with('produk.produk', 'user')->latest();

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->tanggal) {
            try {
                // langsung filter dengan whereDate agar aman
                $query->whereDate('created_at', $this->tanggal);
            } catch (\Exception $e) {
                // abaikan jika tanggal invalid
            }
        }

        $data = $query->get();

        return $data->map(function ($item, $i) {
            return [
                '#' => $i + 1,
                'Kode Produk' => $item->kode ?? '-',
                'Nama Produk' => optional($item->produk?->produk)->nama ?? '-',
                'Jumlah Produk' => $item->jumlah ?? 0,
                'Status' => ucfirst(str_replace('_', ' ', $item->status ?? '-')),
                'Pemesan' => optional($item->user)->name ?? '-',
                'Tanggal' => optional($item->created_at)->translatedFormat('d F Y') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['#','Kode Produk','Nama Produk','Jumlah Produk','Status','Pemesan','Tanggal'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
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
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                $sheet->setAutoFilter('A1:' . $lastColumn . '1');
            }
        ];
    }
}
