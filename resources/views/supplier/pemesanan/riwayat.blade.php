@extends('layouts.master')

@section('title', 'Riwayat Pemesanan')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-purple-700 flex items-center gap-2">
            <i class="fa fa-history"></i>  Riwayat Pemesanan Bahan Baku
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('supplier.riwayat.pdf', request()->only('date_from', 'date_to')) }}" class="btn btn-sm btn-error text-white">
                <i class="fa fa-file-pdf-o mr-1"></i> PDF
            </a>
            <a href="{{ route('supplier.riwayat.excel', request()->only('date_from', 'date_to')) }}" class="btn btn-sm btn-success text-white">
                <i class="fa fa-file-excel-o mr-1"></i> Excel
            </a>
        </div>
    </div>

    {{-- Form Filter Tanggal --}}
    <div class="mb-4 flex items-center gap-2">
        <form method="GET" action="{{ route('supplier.riwayat') }}" class="flex flex-wrap items-center gap-2">
            <label for="date_from" class="font-semibold">Filter Tanggal:</label>
            <input type="date" name="date_from" id="date_from" 
                   class="input input-bordered input-sm"
                   value="{{ old('date_from', $dateFrom?->toDateString()) }}">
            <input type="date" name="date_to" id="date_to" 
                   class="input input-bordered input-sm"
                   value="{{ old('date_to', $dateTo?->toDateString()) }}">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="{{ route('supplier.riwayat') }}" class="btn btn-sm btn-secondary">Reset</a>
        </form>
    </div>

    @if ($riwayat->isEmpty())
        <div class="alert alert-info shadow text-center">Tidak ada riwayat pemesanan.</div>
    @else
        <div class="overflow-x-auto rounded-xl border border-base-200 shadow-lg">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200 text-base-content">
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Bahan Baku</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayat as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-mono text-purple-700">{{ $item->kode ?? '-' }}</td>
                            <td>{{ $item->updated_at?->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ $item->bahanBaku->nama ?? '-' }}</td>
                            <td>{{ number_format($item->jumlah, 2, ',', '.') }}
                                {{ $item->bahanBaku->satuan ?? '' }}</td>
                            @php
                                $badgeMap = [
                                    \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER => 'badge badge-warning text-white',
                                    \App\Models\PermintaanBahanBaku::STATUS_SELESAI => 'badge badge-success text-white',
                                    \App\Models\PermintaanBahanBaku::STATUS_DITOLAK => 'badge badge-error text-white',
                                    \App\Models\PermintaanBahanBaku::STATUS_DIKIRIM => 'badge badge-info text-white',
                                ];
                                $status = strtolower($item->status);
                            @endphp
                            <td>
                                <span class="{{ $badgeMap[$status] ?? 'badge' }}">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
