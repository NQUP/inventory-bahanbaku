@extends('layouts.master')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-primary">Dashboard Admin</h1>
        <p class="text-sm text-gray-500">Ringkasan pemesanan, stok bahan baku, dan aktivitas terbaru</p>
    </div>

    <form method="GET" class="bg-base-200 p-4 rounded-lg shadow-md flex flex-wrap items-end gap-3">
        <div>
            <label for="date_from" class="label-text text-sm font-semibold">Dari Tanggal</label>
            <input type="date" name="date_from" id="date_from" value="{{ old('date_from', $dateFrom) }}" class="input input-bordered input-sm">
        </div>
        <div>
            <label for="date_to" class="label-text text-sm font-semibold">Sampai Tanggal</label>
            <input type="date" name="date_to" id="date_to" value="{{ old('date_to', $dateTo) }}" class="input input-bordered input-sm">
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-ghost">Reset</a>
    </form>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('bahanbaku.index') }}" class="btn btn-sm btn-primary text-white">
            Kelola Bahan Baku
        </a>
        <a href="{{ route('admin.bom.index') }}" class="btn btn-sm btn-outline btn-primary">
            Kelola BOM
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Total Pemesanan</p><p class="text-2xl font-bold">{{ number_format($data->totalPemesanan, 0, ',', '.') }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Belum Selesai</p><p class="text-2xl font-bold text-warning">{{ number_format($data->pemesananBelumSelesai, 0, ',', '.') }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Total Bahan Baku</p><p class="text-2xl font-bold">{{ number_format($data->totalBahanBaku, 0, ',', '.') }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Total Produk</p><p class="text-2xl font-bold">{{ number_format($data->totalProduk, 0, ',', '.') }}</p></div></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Status Pemesanan</h2>
                <canvas id="adminStatusChart" height="130"></canvas>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Top Stok Bahan Baku</h2>
                <canvas id="adminStockChart" height="130"></canvas>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-base">Grafik Pemakaian 6 Bulan</h2>
            <canvas id="grafikPemakaian" height="100"></canvas>
        </div>
    </div>

    <div class="collapse collapse-arrow bg-warning/10 shadow">
        <input type="checkbox" />
        <div class="collapse-title font-bold text-warning-content">
            Bahan Baku Menipis ({{ $data->bahanMenipis->count() }})
        </div>
        <div class="collapse-content">
            @if ($data->bahanMenipis->isEmpty())
                <p class="text-sm text-gray-500">Tidak ada bahan baku yang menipis.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr><th>Nama</th><th>Stok</th><th>Minimum</th></tr></thead>
                        <tbody>
                        @foreach ($data->bahanMenipis as $bahan)
                            <tr>
                                <td>{{ $bahan->nama }}</td>
                                <td>{{ number_format($bahan->stok, 2, ',', '.') }} {{ $bahan->satuan }}</td>
                                <td>{{ number_format($bahan->stok_minimum, 2, ',', '.') }} {{ $bahan->satuan }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-base">Stok Seluruh Bahan Baku</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Nama</th><th>Stok</th><th>Satuan</th><th>EOQ</th><th>ROP</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                    @forelse ($data->semuaBahan as $bahan)
                        <tr>
                            <td>{{ $bahan->nama }}</td>
                            <td>{{ number_format($bahan->stok, 2, ',', '.') }}</td>
                            <td>{{ $bahan->satuan ?? '-' }}</td>
                            <td>{{ $bahan->eoqRop->eoq ?? '-' }}</td>
                            <td>{{ $bahan->eoqRop->rop ?? '-' }}</td>
                            <td><a href="{{ route('admin.eoq-rop.create', $bahan->id) }}" class="btn btn-xs btn-outline btn-primary">Input EOQ/ROP</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-gray-500">Belum ada data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <h2 class="card-title text-base">Pesanan Terbaru</h2>
                <a href="{{ route('admin.pemesanan.index') }}" class="link link-primary text-sm">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Pemesan</th><th>Produk</th><th>Kode</th><th>Jumlah</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                    @forelse ($data->pesananTerbaru as $pesanan)
                        @php
                            $status = strtolower($pesanan->status_admin ?? '-');
                            $badge = match ($status) {
                                'disetujui' => 'badge-success',
                                'pending' => 'badge-warning',
                                'ditolak' => 'badge-error',
                                default => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td>{{ optional($pesanan->user)->name ?? '-' }}</td>
                            <td>{{ optional($pesanan->produk?->produk)->nama ?? '-' }}</td>
                            <td>{{ $pesanan->kode ?? '-' }}</td>
                            <td>{{ number_format($pesanan->jumlah, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $badge }}">{{ ucfirst($status) }}</span></td>
                            <td>{{ $pesanan->created_at->format('d M Y') }}</td>
                            <td>
                                @if ($pesanan->status_admin === 'pending')
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.pemesanan.setujui', $pesanan->id) }}">@csrf<button class="btn btn-xs btn-success">Setujui</button></form>
                                        <form method="POST" action="{{ route('admin.pemesanan.tolak', $pesanan->id) }}">@csrf<button class="btn btn-xs btn-error">Tolak</button></form>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Sudah diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-gray-500">Belum ada pesanan terbaru.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    if (typeof window.Chart === 'undefined') {
        console.warn('Chart.js gagal dimuat');
        return;
    }
    const labelsStok = @json($data->semuaBahan->take(8)->pluck('nama')->values());
    const valuesStok = @json($data->semuaBahan->take(8)->pluck('stok')->values());

    const pending = {{ (int) $data->pemesananBelumSelesai }};
    const selesai = {{ max((int) $data->totalPemesanan - (int) $data->pemesananBelumSelesai, 0) }};

    const statusCtx = document.getElementById('adminStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Belum Selesai', 'Selesai'],
                datasets: [{ data: [pending, selesai], backgroundColor: ['#f59e0b', '#10b981'] }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2 }
        });
    }

    const stockCtx = document.getElementById('adminStockChart');
    if (stockCtx) {
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: labelsStok,
                datasets: [{ label: 'Stok', data: valuesStok, backgroundColor: '#6366f1' }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2, plugins: { legend: { display: false } } }
        });
    }

    const pemakaianCtx = document.getElementById('grafikPemakaian');
    if (pemakaianCtx) {
        new Chart(pemakaianCtx, {
            type: 'line',
            data: {
                labels: @json($data->grafikPemakaian['labels'] ?? []),
                datasets: [{
                    label: 'Pemakaian (kg)',
                    data: @json($data->grafikPemakaian['data'] ?? []),
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.15)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2 }
        });
    }
})();
</script>
@endpush


