<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Permintaan Bahan Baku - Supplier</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }

        table,
        th,
        td {
            border: 1px solid #333;
        }

        th {
            background-color: #f2f2f2;
        }

        th,
        td {
            padding: 8px 10px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>
        Riwayat Permintaan Bahan Baku 
        @if(!empty($dateFrom) && !empty($dateTo))
            (Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }})
        @else
            (Selesai)
        @endif
        - Supplier
    </h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Permintaan</th>
                <th>Nama Bahan Baku</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Tanggal Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($riwayat as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                    <td>{{ $item->bahanBaku->nama ?? '-' }}</td>
                    <td>{{ $item->jumlah }} {{ $item->bahanBaku->satuan ?? '' }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ $item->updated_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
