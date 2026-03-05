@extends('layouts.master')

@section('title', 'Riwayat Permintaan Gudang')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-purple-700 flex items-center gap-2">
                <i class="fa fa-history"></i>  Riwayat Permintaan Bahan Baku
            </h2>

            <div class="flex gap-2">
                <a href="{{ route('gudang.riwayat.export.pdf', request()->only('date_from', 'date_to')) }}" class="btn btn-sm btn-error text-white">
                    <i class="fa fa-file-pdf-o mr-1"></i> PDF
                </a>
                <a href="{{ route('gudang.riwayat.export.excel', request()->only('date_from', 'date_to')) }}" class="btn btn-sm btn-success text-white">
                    <i class="fa fa-file-excel-o mr-1"></i> Excel
                </a>
            </div>
        </div>

        <!-- Filter Tanggal -->
        <form action="{{ route('gudang.permintaan.riwayat') }}" method="GET" class="mb-4 flex flex-wrap items-center gap-2">
            <label for="date_from" class="font-medium">Filter Tanggal:</label>
            <input type="date" id="date_from" name="date_from" value="{{ old('date_from', $dateFrom?->toDateString()) }}" class="input input-bordered input-sm">
            <input type="date" id="date_to" name="date_to" value="{{ old('date_to', $dateTo?->toDateString()) }}" class="input input-bordered input-sm">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="{{ route('gudang.permintaan.riwayat') }}" class="btn btn-sm btn-outline">Reset</a>
        </form>

        @if ($permintaans->isEmpty())
            <div class="alert alert-info shadow text-center">Belum ada permintaan sesuai filter.</div>
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
                            <th>Pemesan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permintaans as $permintaan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $permintaan->kode ?? '-' }}</td>
                                <td>{{ $permintaan->updated_at->format('d-m-Y') }}</td>
                                <td>{{ $permintaan->bahanBaku->nama ?? '-' }}</td>
                                <td>{{ number_format($permintaan->jumlah, 0, ',', '.') }}</td>
                                <td>{{ $permintaan->pemesanan?->user?->name ?? '-' }}</td>
                                @php
                                    $badgeMap = [
                                        \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER => 'badge badge-warning text-white',
                                        \App\Models\PermintaanBahanBaku::STATUS_DISIAPKAN => 'badge badge-success text-white',
                                        \App\Models\PermintaanBahanBaku::STATUS_DITOLAK => 'badge badge-error text-white',
                                        \App\Models\PermintaanBahanBaku::STATUS_DIKIRIM => 'badge badge-info text-white',
                                        \App\Models\PermintaanBahanBaku::STATUS_SELESAI => 'badge badge-neutral text-white',
                                    ];
                                    $status = strtolower($permintaan->status);
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
