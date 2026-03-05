@extends('layouts.master')

@section('title', 'Dashboard Supplier')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-primary">Dashboard Supplier</h1>
        <p class="text-sm text-gray-500">Pantau permintaan bahan baku dari gudang secara real-time</p>
    </div>

    <form method="GET" class="bg-base-200 p-4 rounded-lg shadow-md flex flex-wrap items-end gap-3">
        <div>
            <label for="date_from" class="label-text text-sm font-semibold">Dari Tanggal</label>
            <input type="date" name="date_from" id="date_from" value="{{ old('date_from', $dateFrom?->toDateString()) }}" class="input input-bordered input-sm">
        </div>
        <div>
            <label for="date_to" class="label-text text-sm font-semibold">Sampai Tanggal</label>
            <input type="date" name="date_to" id="date_to" value="{{ old('date_to', $dateTo?->toDateString()) }}" class="input input-bordered input-sm">
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
        <a href="{{ route('supplier.dashboard') }}" class="btn btn-sm btn-ghost">Reset</a>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Total</p><p class="text-2xl font-bold">{{ $total }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Menunggu</p><p class="text-2xl font-bold text-warning">{{ $menunggu }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Dikirim</p><p class="text-2xl font-bold text-info">{{ $dikirim }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Selesai</p><p class="text-2xl font-bold text-success">{{ $selesai }}</p></div></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Distribusi Status</h2>
                <canvas id="supplierStatusChart" height="130"></canvas>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Top Kebutuhan per Bahan</h2>
                <canvas id="supplierBahanChart" height="130"></canvas>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">Permintaan Terbaru</h2>
        <a href="{{ route('supplier.riwayat') }}" class="btn btn-sm btn-primary">Riwayat Pemesanan</a>
    </div>

    @if ($permintaans->isEmpty())
        <p class="text-gray-500 italic">Belum ada permintaan bahan baku.</p>
    @else
        <div class="overflow-x-auto bg-white rounded-xl shadow">
            <table class="table table-zebra w-full">
                <thead>
                    <tr><th>#</th><th>Kode</th><th>Pemesan</th><th>Nama Bahan</th><th>Jumlah</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($permintaans->take(5) as $index => $item)
                    @php
                        $statusClass = match ($item->status) {
                            \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER => 'badge-warning',
                            \App\Models\PermintaanBahanBaku::STATUS_DIKIRIM => 'badge-info',
                            \App\Models\PermintaanBahanBaku::STATUS_SELESAI => 'badge-success',
                            default => 'badge-neutral',
                        };
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->kode ?? '-' }}</td>
                        <td>{{ $item->pemesanan?->user?->name ?? '-' }}</td>
                        <td>{{ $item->bahanBaku->nama ?? '-' }}</td>
                        <td>{{ number_format($item->jumlah, 2, ',', '.') }} kg</td>
                        <td><span class="badge {{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span></td>
                        <td>{{ $item->created_at->format('d M Y') }}</td>
                        <td>
                            @if ($item->status === \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER)
                                <div class="flex gap-2">
                                    <form action="{{ route('supplier.kirim', $item->id) }}" method="POST" onsubmit="return confirm('Kirim permintaan ini sekarang?')">@csrf @method('PUT')<button type="submit" class="btn btn-xs btn-primary">Kirim</button></form>
                                    <form action="{{ route('supplier.hapus', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus permintaan ini?')">@csrf @method('DELETE')<button type="submit" class="btn btn-xs btn-error">Hapus</button></form>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    if (typeof window.Chart === 'undefined') {
        console.warn('Chart.js gagal dimuat');
        return;
    }
    const statusCtx = document.getElementById('supplierStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Menunggu', 'Dikirim', 'Selesai'],
                datasets: [{ data: [{{ (int) $menunggu }}, {{ (int) $dikirim }}, {{ (int) $selesai }}], backgroundColor: ['#f59e0b', '#3b82f6', '#10b981'] }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2 }
        });
    }

    const bahanLabels = @json($permintaans->groupBy(fn($p) => $p->bahanBaku->nama ?? '-')->keys()->take(8)->values());
    const bahanValues = @json($permintaans->groupBy(fn($p) => $p->bahanBaku->nama ?? '-')->map(fn($rows) => round($rows->sum('jumlah'), 2))->values()->take(8)->values());

    const bahanCtx = document.getElementById('supplierBahanChart');
    if (bahanCtx) {
        new Chart(bahanCtx, {
            type: 'bar',
            data: { labels: bahanLabels, datasets: [{ label: 'Jumlah (kg)', data: bahanValues, backgroundColor: '#6366f1' }] },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2, plugins: { legend: { display: false } } }
        });
    }
})();
</script>
@endpush


