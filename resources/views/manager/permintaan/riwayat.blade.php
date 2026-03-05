@extends('layouts.master')

@section('title', ' Riwayat Permintaan Bahan Baku')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold text-purple-700 mb-6 flex items-center gap-2">
         Riwayat Permintaan Bahan Baku
    </h2>

    {{-- Filter Tanggal --}}
    <form method="GET" action="{{ route('manager.permintaan.riwayat') }}" class="flex flex-wrap items-center gap-2 mb-4">
        <input type="date" name="date_from" value="{{ old('date_from', $dateFrom?->toDateString()) }}" class="input input-bordered">
        <input type="date" name="date_to" value="{{ old('date_to', $dateTo?->toDateString()) }}" class="input input-bordered">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('manager.permintaan.riwayat') }}" class="btn btn-ghost">Reset</a>
    </form>

    {{-- Tombol Export --}}
    <div class="flex justify-end gap-2 mb-4">
      <a href="{{ route('manager.export.manager.excel', request()->only('date_from', 'date_to')) }}" class="btn btn-success gap-2">
    <i class="fa fa-file-excel-o"></i> Export Excel
</a>
        <a href="{{ route('manager.export.manager.pdf', request()->only('date_from', 'date_to')) }}" class="btn btn-error gap-2">
            <i class="fa fa-file-pdf-o"></i> Export PDF
        </a>
    </div>

    {{-- Tabel Riwayat --}}
    <div class="overflow-x-auto bg-base-100 shadow rounded-lg">
        <table class="table table-zebra w-full">
            <thead class="bg-purple-100 text-gray-700">
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th> Produk</th>
                    <th> Bahan Baku</th>
                    <th> Jumlah</th>
                    <th> Status</th>
                    <th> Pemesan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permintaans as $permintaan)
                    <tr>
                        <td class="font-mono text-sm text-purple-700">{{ $permintaan->kode }}</td>
                        <td>{{ $permintaan->created_at->format('d M Y') }}</td>
                        <td>{{ $permintaan->pemesanan?->produk?->produk?->nama ?? '-' }}</td>
                        <td>{{ $permintaan->bahanBaku->nama ?? '-' }}</td>
                        <td>{{ number_format($permintaan->jumlah, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $statusMap = [
                                    \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER => ['badge-warning', 'fa fa-hourglass-half'],
                                    \App\Models\PermintaanBahanBaku::STATUS_DITOLAK => ['badge-error', 'fa fa-times-circle'],
                                    \App\Models\PermintaanBahanBaku::STATUS_SELESAI => ['badge-success', 'fa fa-check-circle'],
                                    \App\Models\PermintaanBahanBaku::STATUS_DIKIRIM => ['badge-info', 'fa fa-truck'],
                                    'diterima_gudang' => ['badge-accent', 'fa fa-inbox'],
                                    'unknown' => ['badge-neutral', 'fa fa-file-text-o'],
                                ];
                                [$color, $icon] = $statusMap[$permintaan->status] ?? $statusMap['unknown'];
                            @endphp
                            <span class="badge {{ $color }}">
                                <i class="{{ $icon }} mr-1"></i>{{ strtoupper(str_replace('_', ' ', $permintaan->status)) }}
                            </span>
                        </td>
                        <td>{{ $permintaan->pemesanan?->user?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 py-4"> Tidak ada riwayat permintaan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

