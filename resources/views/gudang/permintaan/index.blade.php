@extends('layouts.master')

@section('title', 'Permintaan Gudang')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <h3 class="text-2xl font-bold text-purple-700 mb-6 flex items-center gap-2">
            <i class="fa fa-cube"></i>  Permintaan Bahan Baku Gudang
        </h3>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded shadow">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabel Permintaan --}}
        @if ($permintaans->isEmpty())
            <div class="p-4 bg-purple-100 text-purple-800 rounded shadow">
                Tidak ada permintaan yang perlu diproses.
            </div>
        @else
            <div class="overflow-x-auto bg-white shadow rounded">
                <table class="min-w-full text-sm text-left text-gray-700">
                    <thead class="bg-purple-100 text-purple-800 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Kode</th>
                            <th class="px-4 py-2">Produk</th>
                            <th class="px-4 py-2">Jumlah</th>
                            <th class="px-4 py-2">Pemesan</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permintaans as $index => $p)
                            @php
                                $badgeColors = [
                                    \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_SUPPLIER => 'badge badge-warning',
                                    \App\Models\PermintaanBahanBaku::STATUS_DIKIRIM => 'badge badge-purple',
                                    \App\Models\PermintaanBahanBaku::STATUS_SELESAI => 'badge badge-success',
                                    \App\Models\PermintaanBahanBaku::STATUS_DITOLAK => 'badge badge-error',
                                ];
                                $statusColor = $badgeColors[$p->status] ?? 'badge badge-neutral';
                            @endphp
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $index + 1 }}</td>
                                <td class="px-4 py-2">{{ $p->kode }}</td> <!--  Tambahan -->
                                <td class="px-4 py-2">
                                    {{ $p->bahanBaku->nama ?? '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ number_format($p->jumlah) }} {{ $p->bahanBaku->satuan ?? '-' }}
                                </td>
                                <td class="px-4 py-2">{{ $p->pemesanan?->user?->name ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <span class="{{ $statusColor }} text-xs font-semibold px-3 py-1 rounded-full">
                                        {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    @if ($p->status === \App\Models\PermintaanBahanBaku::STATUS_DIKIRIM)
                                        <form action="{{ route('gudang.permintaan.terima', $p->id) }}" method="POST"
                                            onsubmit="return confirm('Tandai bahan ini sudah diterima?')" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success flex items-center gap-1">
                                                <i class="fa fa-check-circle"></i> Terima
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
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
