@extends('layouts.master')

@section('title', 'Riwayat Pemesanan Produk Jadi')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-purple-700 flex items-center gap-2">
            <i class="fa fa-history"></i>  Riwayat Pemesanan Produk Jadi
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.pemesanan.export', request()->only('tanggal')) }}" class="btn btn-sm btn-error text-white">
                <i class="fa fa-file-pdf-o mr-1"></i> PDF
            </a>
            <a href="{{ route('admin.pemesanan.exportExcel', request()->only('tanggal')) }}" class="btn btn-sm btn-success text-white">
                <i class="fa fa-file-excel-o mr-1"></i> Excel
            </a>
        </div>
    </div>

    {{-- Form Filter Tanggal Tunggal --}}
    <div class="mb-4 flex items-center gap-2">
        <form method="GET" action="{{ route('admin.pemesanan.index') }}" class="flex items-center gap-2">
            <label for="tanggal" class="font-semibold">Filter Tanggal:</label>
            <input type="date" name="tanggal" id="tanggal" 
                   class="input input-bordered input-sm"
                   value="{{ request('tanggal') }}">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="{{ route('admin.pemesanan.index') }}" class="btn btn-sm btn-secondary">Reset</a>
        </form>
    </div>

    @if ($pemesanans->isEmpty())
        <div class="alert alert-info shadow text-center">Tidak ada riwayat pemesanan.</div>
    @else
        <div class="overflow-x-auto rounded-xl border border-base-200 shadow-lg">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200 text-base-content">
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Pemesan</th>
                        <th>Produk Jadi</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pemesanans as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-mono text-purple-700">{{ $p->kode ?? '-' }}</td>
                            {{-- Ganti updated_at menjadi created_at agar sesuai filter --}}
                            <td>{{ $p->created_at?->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ optional($p->user)->name ?? '-' }}</td>
                            <td>{{ optional($p->produk->produk)->nama ?? '-' }}</td>
                            <td>{{ number_format($p->jumlah, 0, ',', '.') }}</td>
                            @php
                                $badgeMap = [
                                    'pending' => 'badge badge-warning text-black',
                                    'disetujui' => 'badge badge-success text-white',
                                    'ditolak' => 'badge badge-error text-white',
                                    'selesai' => 'badge badge-info text-white',
                                ];
                                $status = strtolower($p->status_admin ?? 'pending');
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
