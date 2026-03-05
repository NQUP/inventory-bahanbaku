@extends('layouts.master')

@section('content')
    <div class="container">
        <h1 class="mb-4">Daftar Permintaan Bahan Baku</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Bahan Baku</th>
                    <th>Jumlah Dibutuhkan</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permintaans as $permintaan)
                    <tr>
                        <td>{{ $permintaan->bahanBaku->nama }}</td>
                        <td>{{ $permintaan->jumlah }}</td>
                        <td>
                            @if ($permintaan->status === \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_PERSETUJUAN_MANAGER)
                                <span class="badge bg-warning">Menunggu</span>
                            @elseif($permintaan->status === \App\Models\PermintaanBahanBaku::STATUS_DISETUJUI)
                                <span class="badge bg-success">Disetujui</span>
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                            @endif
                        </td>
                        <td>{{ $permintaan->pembuat->name ?? '-' }}</td>
                        <td>
                            @if ($permintaan->status === \App\Models\PermintaanBahanBaku::STATUS_MENUNGGU_PERSETUJUAN_MANAGER)
                                <form method="POST" action="{{ route('admin.permintaan.setujui', $permintaan->id) }}"
                                    style="display:inline;">
                                    @csrf
                                    <button class="btn btn-success btn-sm">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('admin.permintaan.tolak', $permintaan->id) }}"
                                    style="display:inline;">
                                    @csrf
                                    <button class="btn btn-danger btn-sm">Tolak</button>
                                </form>
                            @else
                                <em>Tidak ada aksi</em>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Tidak ada permintaan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
