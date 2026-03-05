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

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Produk</th>
                <th>Nama Bahan Baku</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Pemesan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permintaans as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left">{{ $p->pemesanan->produk?->produk?->nama ?? '-' }}</td>
                    <td class="text-left">{{ $p->bahanBaku?->nama ?? '-' }}</td>
                    <td>{{ number_format($p->jumlah, 0, ',', '.') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $p->status)) }}</td>
                    <td>{{ $p->pemesanan->user?->name ?? '-' }}</td>
                    <td>{{ $p->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
