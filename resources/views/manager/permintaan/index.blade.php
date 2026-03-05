@extends('layouts.master')

@section('title', 'Daftar Permintaan Bahan Baku')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-4">Daftar Permintaan Bahan Baku</h1>

        {{-- Flash Message --}}
        @if (session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning mb-4">
                {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info mb-4">
                {{ session('info') }}
            </div>
        @endif

        {{-- Statistik --}}
        <div class="mb-4">
            <div class="stats shadow bg-base-100">
                <div class="stat">
                    <div class="stat-title">Total Permintaan</div>
                    <div class="stat-value text-primary">{{ $permintaans->count() }}</div>
                </div>
            </div>
        </div>

        {{-- Tabel Permintaan --}}
        <div class="overflow-x-auto bg-base-100 rounded-box shadow">
            <table class="table table-zebra">
                <thead class="bg-base-200 text-base-content">
                    <tr>
                        <th>#</th>
                        <th>Kode</th> {{-- Tambahan --}}
                        <th>Nama Bahan Baku</th>
                        <th>Jumlah Dibutuhkan</th>
                        <th>Supplier</th>
                        <th>EOQ</th>
                        <th>ROP</th>
                        <th>Status</th>
                        <th>Dibuat Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permintaans as $index => $permintaan)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $permintaan->pemesanan?->kode ?? '-' }}</td> {{-- Tambahan --}}
                            <td>{{ $permintaan->bahanBaku->nama ?? '-' }}</td>
                            <td>
                                {{ number_format($permintaan->jumlah, 2, ',', '.') }}
                                {{ $permintaan->bahanBaku->satuan ?? 'Unit' }}
                            </td>
                            <td>{{ $permintaan->bahanBaku->supplier->name ?? '-' }}</td>
                            <td>{{ $permintaan->bahanBaku->eoqRop->eoq ?? '-' }}</td>
                            <td>{{ $permintaan->bahanBaku->eoqRop->rop ?? '-' }}</td>
                            <td>
                            @switch($permintaan->status)
                                    @case(\App\Models\PermintaanBahanBaku::STATUS_DISETUJUI)
                                        <span class="badge badge-success">Disetujui</span>
                                    @break

                                    @case(\App\Models\PermintaanBahanBaku::STATUS_DITOLAK)
                                        <span class="badge badge-error">Ditolak</span>
                                    @break

                                    @case(\App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER)
                                        <span class="badge badge-warning">Menunggu Supplier</span>
                                    @break

                                    @default
                                        <span class="badge badge-neutral">{{ ucfirst($permintaan->status ?? 'pending') }}</span>
                                @endswitch
                            </td>
                            <td>{{ $permintaan->created_at->format('d M Y H:i') }}</td>
                            <td class="flex gap-1">
                                @if ($permintaan->status === \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_PERSETUJUAN_MANAGER)
                                    {{-- Setujui --}}
                                    <form action="{{ route('manager.permintaan.setujui', $permintaan->id) }}"
                                        method="POST">
                                        @csrf
                                        <button class="btn btn-success btn-xs" type="submit">Setujui</button>
                                    </form>

                                    {{-- Tolak --}}
                                    <form action="{{ route('manager.permintaan.tolak', $permintaan->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-error btn-xs" type="submit">Tolak</button>
                                    </form>
                                @else
                                    <span class="text-sm text-gray-500 italic">Sudah diproses</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">Tidak ada permintaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endsection
