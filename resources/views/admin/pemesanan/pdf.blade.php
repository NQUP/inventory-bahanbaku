<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Permintaan Bahan Baku</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 5px;
        }

        th {
            background-color: #f3f3f3;
            text-align: center;
        }

        td {
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Laporan Permintaan Bahan Baku</h2>

    @if(!empty($tanggal))
        <p>Tanggal: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode</th>
                <th>Produk</th>
                <th>Jumlah (gr)</th>
                <th>Status</th>
                <th>Pemesan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pemesanans as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $p->kode ?? '-' }}</td>
                    <td>{{ optional($p->produk->produk)->nama ?? '-' }}</td>
                    <td>{{ number_format($p->jumlah, 2, ',', '.') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $p->status)) }}</td>
                    <td>{{ optional($p->user)->name ?? '-' }}</td>
                    <td>{{ $p->created_at?->format('d-m-Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">Tidak ada data permintaan untuk tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
