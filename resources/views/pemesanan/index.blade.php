@extends('layouts.master')

@section('content')
    <div class="max-w-7xl mx-auto p-6 space-y-6">

        {{-- Judul --}}
        <div class="space-y-1">
            <h1 class="text-3xl font-bold text-purple-600 flex items-center gap-2">
                <i class="fa fa-clipboard"></i> Daftar Pemesanan Bahan Baku
            </h1>
            <p class="text-gray-500">Kelola dan pantau pemesanan bahan baku manual maupun otomatis (JIT).</p>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success bg-green-100 text-green-800 border border-green-300 shadow">
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Tombol Aksi --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('pemesanan.create') }}" class="btn btn-primary">
                <i class="fa fa-plus-circle"></i> Tambah Pemesanan
            </a>

            <a href="{{ route('pemesanan.otomatis') }}" class="btn btn-success">
                <i class="fa fa-cogs"></i> Pemesanan Otomatis (JIT)
            </a>

            <a href="{{ route('export.pemesanan.excel') }}" class="btn btn-accent">
                <i class="fa fa-file-excel-o"></i> Export Excel
            </a>

            <a href="{{ route('export.pemesanan.pdf') }}" class="btn btn-error">
                <i class="fa fa-file-pdf-o"></i> Export PDF
            </a>
        </div>

        {{-- Tabel Pemesanan --}}
        <div class="overflow-x-auto bg-white rounded-xl shadow">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200 text-base-content">
                    <tr>
                        <th>Kode</th>
                        <th>Bahan Baku</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemesanans as $pemesanan)
                        <tr>
                            <td class="text-center">{{ $pemesanan->kode }}</td>
                            <td>{{ $pemesanan->bahanBaku->nama ?? '-' }}</td>
                            <td class="text-center">{{ number_format($pemesanan->jumlah) }}</td>
                            <td class="text-center">
                                <form action="{{ route('pemesanan.updateStatus', $pemesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                        class="select select-sm select-bordered">
                                        <option value="pending" {{ $pemesanan->status == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="diterima" {{ $pemesanan->status == 'diterima' ? 'selected' : '' }}>
                                            Diterima</option>
                                        <option value="ditolak" {{ $pemesanan->status == 'ditolak' ? 'selected' : '' }}>
                                            Ditolak</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-center">{{ $pemesanan->created_at->format('d-m-Y') }}</td>
                            <td class="text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="{{ route('pemesanan.edit', $pemesanan->id) }}"
                                        class="btn btn-sm btn-outline btn-info">
                                        <i class="fa fa-pencil-square-o"></i>
                                    </a>
                                    <form action="{{ route('pemesanan.destroy', $pemesanan->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus pemesanan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline btn-error">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-4 italic">Belum ada data pemesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
