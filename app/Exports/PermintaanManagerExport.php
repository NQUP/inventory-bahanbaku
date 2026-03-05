<?php

namespace App\Exports;

use App\Models\PermintaanBahanBaku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;

class PermintaanManagerExport implements FromCollection, WithHeadings
{
    protected $tanggal;

    public function __construct($tanggal = null)
    {
        $this->tanggal = $tanggal;
    }

    public function collection()
    {
        $query = PermintaanBahanBaku::with(['bahanBaku', 'bahanBaku.supplier', 'bahanBaku.eoqRop']);

        if (!empty($this->tanggal)) {
            $query->whereDate('created_at', $this->tanggal);
        }

        return $query->get()->map(function ($p) {
            return [
                'Nama Bahan Baku' => $p->bahanBaku->nama ?? '-',
                'Jumlah' => $p->jumlah,
                'Satuan' => $p->bahanBaku->satuan ?? '-',
                'Supplier' => $p->bahanBaku->supplier->name ?? '-',
                'EOQ' => $p->bahanBaku->eoqRop->eoq ?? '-',
                'ROP' => $p->bahanBaku->eoqRop->rop ?? '-',
                'Status' => ucfirst(str_replace('_', ' ', $p->status)),
                'Tanggal Permintaan' => $p->created_at->format('d-m-Y'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Bahan Baku',
            'Jumlah',
            'Satuan',
            'Supplier',
            'EOQ',
            'ROP',
            'Status',
            'Tanggal Permintaan',
        ];
    }
}
