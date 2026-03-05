@extends('layouts.master')

@section('title', 'Dashboard Gudang')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-primary">Dashboard Gudang</h1>
        <p class="text-sm text-gray-500">Monitoring status permintaan dan distribusi bahan baku</p>
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
        <a href="{{ route('gudang.dashboard') }}" class="btn btn-sm btn-ghost">Reset</a>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Total Permintaan</p><p class="text-2xl font-bold">{{ $totalPermintaan }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Menunggu</p><p class="text-2xl font-bold text-warning">{{ $menunggu }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Dikirim</p><p class="text-2xl font-bold text-info">{{ $dikirim }}</p></div></div>
        <div class="card bg-base-100 shadow"><div class="card-body"><p class="text-sm">Selesai</p><p class="text-2xl font-bold text-success">{{ $selesai }}</p></div></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Distribusi Status</h2>
                <canvas id="gudangStatusChart" height="130"></canvas>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-base">Jumlah per Permintaan Terbaru</h2>
                <canvas id="gudangTrendChart" height="130"></canvas>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">Permintaan Terbaru</h2>
        <a href="{{ route('gudang.permintaan.riwayat') }}" class="btn btn-sm btn-primary">Riwayat Permintaan</a>
    </div>

    @if ($permintaansTerbaru->isEmpty())
        <p class="text-gray-500 italic">Belum ada data permintaan.</p>
    @else
        <div class="overflow-x-auto bg-white rounded-xl shadow">
            <table class="table table-zebra w-full">
                <thead>
                    <tr><th>#</th><th>Kode</th><th>Nama Bahan</th><th>Jumlah</th><th>Status</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                @foreach ($permintaansTerbaru as $index => $permintaan)
                    @php
                        $badgeMap = [
                            \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER => 'badge-warning',
                            \App\Models\PermintaanBahanBaku::STATUS_DISIAPKAN => 'badge-success',
                            \App\Models\PermintaanBahanBaku::STATUS_DITOLAK => 'badge-error',
                            \App\Models\PermintaanBahanBaku::STATUS_DIKIRIM => 'badge-info',
                            \App\Models\PermintaanBahanBaku::STATUS_SELESAI => 'badge-neutral',
                        ];
                        $badgeClass = $badgeMap[$permintaan->status] ?? 'badge-neutral';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="font-mono">{{ $permintaan->kode ?? '-' }}</td>
                        <td>{{ $permintaan->bahanBaku->nama ?? '-' }}</td>
                        <td>{{ number_format($permintaan->jumlah, 0, ',', '.') }} {{ $permintaan->bahanBaku->satuan ?? '' }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $permintaan->status)) }}</span></td>
                        <td>{{ $permintaan->created_at->format('d M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <a href="{{ route('gudang.permintaan.index') }}" class="btn btn-outline btn-sm btn-primary">Lihat Semua Permintaan</a>
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
    const statusCtx = document.getElementById('gudangStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Menunggu', 'Dikirim', 'Selesai', 'Ditolak'],
                datasets: [{ data: [{{ (int) $menunggu }}, {{ (int) $dikirim }}, {{ (int) $selesai }}, {{ (int) $ditolak }}], backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'] }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2 }
        });
    }

    const trendCtx = document.getElementById('gudangTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($permintaansTerbaru->reverse()->values()->map(fn($p) => ($p->kode ?? 'ID-'.$p->id))->values()),
                datasets: [{ label: 'Jumlah', data: @json($permintaansTerbaru->reverse()->values()->pluck('jumlah')->values()), borderColor: '#8b5cf6', backgroundColor: 'rgba(139, 92, 246, 0.15)', fill: true, tension: 0.3 }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2 }
        });
    }
})();
</script>
@endpush


