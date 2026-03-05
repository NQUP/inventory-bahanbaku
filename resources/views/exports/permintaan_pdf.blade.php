<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Permintaan Bahan Baku</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .tanggal {
            text-align: center;
            margin-bottom: 10px;
            font-size: 12px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-left {
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Laporan Permintaan Bahan Baku</h2>

    @if(!empty($dateFrom) && !empty($dateTo))
        <div class="tanggal">Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</div>
    @else
        <div class="tanggal">Periode: Semua Data</div>
    @endif

   <table>
    <thead>
        <tr>
            <th>#</th>
            <th>Kode</th>
            <th>Nama Produk</th>
            <th>Nama Bahan Baku</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Pemesan</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($permintaans as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="font-mono text-sm text-purple-700">{{ $p->kode }}</td>
                <td class="text-left">{{ $p->pemesanan->produk?->produk?->nama ?? '-' }}</td>
                <td class="text-left">{{ $p->bahanBaku?->nama ?? '-' }}</td>
                <td>{{ number_format($p->jumlah, 0, ',', '.') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $p->status)) }}</td>
                <td>{{ $p->pemesanan->user?->name ?? '-' }}</td>
                <td>{{ $p->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #999;">Tidak ada data permintaan.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>

</html>
