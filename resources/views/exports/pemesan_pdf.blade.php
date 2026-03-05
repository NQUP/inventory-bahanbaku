<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pemesanan Produk</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 20px; }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; }
        .small { font-size: 11px; }
    </style>
</head>

<body>
    <h2>Laporan Pemesanan Produk</h2>

    @php
        $statusFilter = $status ?? null;
        $tanggalFilter = $tanggal ?? null;
    @endphp

    @if($statusFilter || $tanggalFilter)
        <p class="small">
            Filter:
            @if($statusFilter)
                Status: {{ $statusFilter }}
            @endif
            @if($tanggalFilter)
                @if($statusFilter), @endif
                Tanggal: {{ \Carbon\Carbon::parse($tanggalFilter)->translatedFormat('d F Y') }}
            @endif
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Jumlah Produk</th>
                <th>Status</th>
                <th>Pemesan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($query as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->kode }}</td>
                    <td class="text-left">{{ $p->produk?->produk?->nama ?? '—' }}</td>
                    <td>{{ number_format($p->jumlah, 0, ',', '.') }}</td>
                    <td class="text-left">{{ ucfirst(str_replace('_', ' ', $p->status)) }}</td>
                    <td class="text-left">{{ $p->user?->name ?? '—' }}</td>
                    <td>{{ $p->created_at->translatedFormat('d F Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data pemesanan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
