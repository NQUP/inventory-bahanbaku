@extends('layouts.master')

@section('title', 'Dashboard Pemesanan')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-primary">Dashboard Pemesanan Produk Jadi</h1>
        <p class="text-sm text-gray-500">Ringkasan order Anda dengan visualisasi Chart.js</p>
    </div>

    <form method="GET" class="bg-base-200 p-4 rounded-lg shadow-md flex flex-wrap items-end gap-4">
        <div class="flex items-center gap-2">
            <label for="status" class="font-semibold">Status:</label>
            <select name="status" id="status" class="select select-bordered select-sm">
                <option value="">Semua</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Dikirim" {{ request('status') == 'Dikirim' ? 'selected' : '' }}>Dikirim</option>
                <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label for="date_from" class="font-semibold">Dari:</label>
            <input type="date" name="date_from" id="date_from" value="{{ old('date_from', $dateFrom?->toDateString()) }}" class="input input-bordered input-sm">
        </div>
        <div class="flex items-center gap-2">
            <label for="date_to" class="font-semibold">Sampai:</label>
            <input type="date" name="date_to" id="date_to" value="{{ old('date_to', $dateTo?->toDateString()) }}" class="input input-bordered input-sm">
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <a href="{{ route('pemesanan.dashboard') }}" class="btn btn-sm btn-ghost">Reset</a>
    </form>

    <div class="flex flex-wrap justify-between items-center gap-3">
        <a href="{{ route('pemesanan.create') }}" class="btn btn-primary">Tambah Pemesanan</a>
        <div class="flex gap-2">
            <a href="{{ route('pemesanan.export.pdf', request()->query()) }}" class="btn btn-outline btn-error">Export PDF</a>
            <a href="{{ route('pemesanan.export.excel', request()->query()) }}" class="btn btn-outline btn-success">Export Excel</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Total Unit</p><p class="text-2xl font-bold">{{ number_format($total, 0, ',', '.') }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Belum Selesai</p><p class="text-2xl font-bold text-warning">{{ $belumSelesai }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Selesai</p><p class="text-2xl font-bold text-success">{{ $selesai }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Total Bahan Baku</p><p class="text-2xl font-bold text-primary">{{ number_format($bahanBakuTotal, 2, ',', '.') }} kg</p></div></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Komposisi Status</h2>
                <canvas id="pemesanStatusChart" height="130"></canvas>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Jumlah Unit per Kode</h2>
                <canvas id="pemesanTrendChart" height="130"></canvas>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow">
        <table class="table table-zebra w-full">
            <thead>
                <tr><th>#</th><th>Kode Produk</th><th>Nama Produk</th><th>Jumlah</th><th>Tipe</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse ($pemesanans as $p)
                    @php
                        $jumlahUnit = $p->jumlah ?? 0;
                        $totalBahanGram = $p->produk?->details->sum(fn($d) => $d->jumlah_per_produk * $jumlahUnit) ?? 0;
                        $totalBahanKg = $totalBahanGram / 1000;
                        $badgeClass = match ($p->status) {
                            'Selesai' => 'badge-success',
                            'Dikirim' => 'badge-info',
                            'Pending' => 'badge-warning',
                            default => 'badge-neutral',
                        };
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration + ($pemesanans->currentPage() - 1) * $pemesanans->perPage() }}</td>
                        <td>{{ $p->kode ?? '-' }}</td>
                        <td>{{ $p->produk?->produk?->nama ?? '-' }}</td>
                        <td>{{ number_format($p->jumlah) }} Unit<br><span class="text-xs text-gray-500">{{ number_format($totalBahanKg, 2) }} kg bahan baku</span></td>
                        <td>{{ $p->tipe ?? '-' }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $p->status }}</span></td>
                        <td>{{ $p->created_at->format('d M Y') }}</td>
                        <td>
                            @if ($p->status === 'Pending')
                                <div class="flex gap-2">
                                    <a href="{{ route('pemesanan.edit', $p->id) }}" class="btn btn-xs btn-warning">Edit</a>
                                    <form action="{{ route('pemesanan.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pemesanan ini?')">@csrf @method('DELETE')<button type="submit" class="btn btn-xs btn-error">Hapus</button></form>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-gray-500 py-4 italic">Belum ada data pemesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $pemesanans->withQueryString()->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    if (typeof window.Chart === 'undefined') {
        console.warn('Chart.js gagal dimuat');
        return;
    }
    const statusCtx = document.getElementById('pemesanStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Belum Selesai', 'Selesai'],
                datasets: [{ data: [{{ (int) $belumSelesai }}, {{ (int) $selesai }}], backgroundColor: ['#f59e0b', '#10b981'] }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2 }
        });
    }

    const trendCtx = document.getElementById('pemesanTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: @json($pemesanans->pluck('kode')->map(fn($k) => $k ?? '-')->values()),
                datasets: [{ label: 'Jumlah Unit', data: @json($pemesanans->pluck('jumlah')->values()), backgroundColor: '#6366f1' }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2, plugins: { legend: { display: false } } }
        });
    }
})();
</script>
@endpush


