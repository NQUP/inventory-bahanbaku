@extends('layouts.master')

@section('content')
    <div class="container">
        <h1 class="mb-4">Daftar Bill of Materials (BOM)</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('admin.bom.create') }}" class="btn btn-primary mb-3">+ Tambah BOM</a>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Bahan Baku</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($boms as $index => $bom)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $bom->nama_produk }}</td>
                                <td>
                                    <ul>
                                        @foreach ($bom->details as $detail)
                                            <li>{{ $detail->bahanBaku->nama_bahan }} - {{ $detail->jumlah_per_produk }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <a href="{{ route('admin.bom.edit', $bom->id) }}" class="btn btn-sm btn-warning">Edit</a>

                                    <form action="{{ route('admin.bom.destroy', $bom->id) }}" method="POST"
                                        style="display:inline-block;"
                                        onsubmit="return confirm('Yakin ingin menghapus BOM ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data BOM.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
