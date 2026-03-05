@extends('layouts.master')

@section('title', 'Dashboard Manager')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-primary">Dashboard Manager</h1>
        <p class="text-sm text-gray-500">Ringkasan permintaan bahan baku dan parameter EOQ/ROP</p>
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
        <a href="{{ route('manager.dashboard') }}" class="btn btn-sm btn-ghost">Reset</a>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <p class="text-sm">Total Permintaan Menunggu Persetujuan</p>
                <p class="text-3xl font-bold text-warning">{{ $totalPermintaan }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <p class="text-sm">Data EOQ/ROP Tersedia</p>
                <p class="text-3xl font-bold text-primary">{{ $eoqRopList->count() }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Distribusi Status Permintaan</h2>
                <canvas id="managerStatusChart" height="130"></canvas>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Top EOQ per Bahan</h2>
                <canvas id="managerEoqChart" height="130"></canvas>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">Permintaan Terbaru (Menunggu Persetujuan)</h2>
        <a href="{{ route('manager.permintaan.riwayat') }}" class="btn btn-sm btn-primary">Riwayat Persetujuan</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success"><span>{{ session('success') }}</span></div>
    @elseif (session('error'))
        <div class="alert alert-error"><span>{{ session('error') }}</span></div>
    @elseif (session('info'))
        <div class="alert alert-info"><span>{{ session('info') }}</span></div>
    @endif

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            @if ($permintaans->isEmpty())
                <p class="text-sm text-gray-500">Belum ada permintaan yang menunggu persetujuan.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra text-sm">
                        <thead>
                            <tr><th>#</th><th>Produk</th><th>Bahan Baku</th><th class="text-right">Jumlah</th><th class="text-right">EOQ</th><th class="text-right">ROP</th><th>Pemesan</th><th>Tanggal</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($permintaans as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $p->pemesanan?->produk?->produk?->nama ?? '-' }}</td>
                                <td>{{ $p->bahanBaku?->nama ?? '-' }}</td>
                                <td class="text-right">{{ number_format($p->jumlah, 2, ',', '.') }} {{ $p->bahanBaku?->satuan ?? '-' }}</td>
                                <td class="text-right">{{ $p->bahanBaku?->eoqRop?->eoq ?? '-' }}</td>
                                <td class="text-right">{{ $p->bahanBaku?->eoqRop?->rop ?? '-' }}</td>
                                <td>{{ $p->pemesanan?->user?->name ?? '-' }}</td>
                                <td>{{ $p->created_at->translatedFormat('d M Y') }}</td>
                                <td><span class="badge badge-outline">{{ Str::headline(str_replace('_', ' ', $p->status)) }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-right mt-4">
                    <a href="{{ route('manager.permintaan.index') }}" class="link link-primary">Lihat Semua Permintaan</a>
                </div>
            @endif
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
    const statusMap = {
        menunggu_persetujuan_manager: 0,
        menunggu_supplier: 0,
        disetujui: 0,
        ditolak: 0,
    };

    statusMap.menunggu_persetujuan_manager = {{ (int) ($statusCounts['menunggu_persetujuan_manager'] ?? 0) }};
    statusMap.menunggu_supplier = {{ (int) ($statusCounts['menunggu_supplier'] ?? 0) }};
    statusMap.disetujui = {{ (int) ($statusCounts['disetujui'] ?? 0) }};
    statusMap.ditolak = {{ (int) ($statusCounts['ditolak'] ?? 0) }};

    const statusCtx = document.getElementById('managerStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Menunggu Manager', 'Menunggu Supplier', 'Disetujui', 'Ditolak'],
                datasets: [{
                    data: [statusMap.menunggu_persetujuan_manager, statusMap.menunggu_supplier, statusMap.disetujui, statusMap.ditolak],
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2 }
        });
    }

    const eoqLabels = @json($eoqRopList->take(8)->map(fn($r) => $r->bahanBaku->nama ?? 'Tanpa Nama')->values());
    const eoqValues = @json($eoqRopList->take(8)->pluck('eoq')->map(fn($v) => (float) ($v ?? 0))->values());

    const eoqCtx = document.getElementById('managerEoqChart');
    if (eoqCtx) {
        new Chart(eoqCtx, {
            type: 'bar',
            data: {
                labels: eoqLabels,
                datasets: [{ label: 'EOQ', data: eoqValues, backgroundColor: '#8b5cf6' }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2, plugins: { legend: { display: false } } }
        });
    }
})();
</script>
@endpush


