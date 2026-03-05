<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Riwayat Permintaan Gudang</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>

<body>
    <h2>Riwayat Permintaan Bahan Baku - Gudang</h2>
    <p>
        @if(!empty($dateFrom) && !empty($dateTo))
            Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}
        @else
            Periode: Semua Data
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Kode</th>
                <th>Nama Bahan Baku</th>
                <th>Jumlah</th>
                <th>Pemesan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permintaans as $permintaan)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $permintaan->updated_at->format('d-m-Y') }}</td>
                    <td>{{ $permintaan->kode ?? '-' }}</td>
                    <td>{{ $permintaan->bahanBaku->nama ?? '-' }}</td>
                    <td>{{ number_format($permintaan->jumlah, 0, ',', '.') }}</td>
                    <td>{{ $permintaan->pemesanan->user->name ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $permintaan->status)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
