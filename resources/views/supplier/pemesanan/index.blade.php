@extends('layouts.master')

@section('title', 'Daftar Pemesanan Bahan Baku')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="mb-6 text-3xl font-bold text-purple-700 flex items-center gap-2">
             Daftar Pemesanan Bahan Baku
        </h1>

        @if (session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($pemesanans->isEmpty())
            <div class="alert alert-info">
                Belum ada permintaan bahan baku.
            </div>
        @else
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="table w-full table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode Permintaan</th> {{--  Tambahan --}}
                            <th>Nama Bahan Baku</th>
                            <th>Jumlah</th>
                            <th>Status Pengiriman</th>
                            <th>Tanggal Dikirim</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pemesanans as $index => $pesan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-mono text-sm text-purple-700">{{ $pesan->kode ?? '-' }}</td>
                                {{--  Kode --}}
                                <td>{{ $pesan->bahan_baku->nama ?? '-' }}</td>
                                <td>{{ $pesan->jumlah }} {{ $pesan->bahan_baku->satuan ?? '' }}</td>
                                <td>
                                    @if ($pesan->status_pengiriman == 'dikirim')
                                        <span class="badge badge-success"><i class="fa fa-truck mr-1"></i>Dikirim</span>
                                    @elseif($pesan->status_pengiriman == 'diterima')
                                        <span class="badge badge-primary"><i class="fa fa-check-circle mr-1"></i>Diterima</span>
                                    @else
                                        <span class="badge badge-warning text-black"><i class="fa fa-hourglass-half mr-1"></i>Menunggu</span>
                                    @endif
                                </td>
                                <td>{{ $pesan->tanggal_dikirim ? \Carbon\Carbon::parse($pesan->tanggal_dikirim)->format('d-m-Y H:i') : '-' }}
                                </td>
                                <td>
                                    @if ($pesan->status_pengiriman == 'menunggu')
                                        <form action="{{ route('supplier.kirim', $pesan->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin kirim bahan ini ke Gudang?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-primary flex items-center gap-1">
                                                <i class="fa fa-paper-plane"></i> Kirim
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 italic text-sm">Tidak ada aksi</span>
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
