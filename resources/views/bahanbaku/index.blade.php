@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Daftar Bahan Baku</h1>
    <a href="{{ route('bahanbaku.create') }}" class="btn btn-primary mb-3">Tambah Bahan Baku</a>
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Stok</th>
                <th>Stok Minimum</th>
                <th>Satuan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bahanbakus as $bahanbaku)
            <tr>
                <td>{{ $bahanbaku->nama }}</td>
                <td>{{ $bahanbaku->stok }}</td>
                <td>{{ $bahanbaku->stok_minimum }}</td>
                <td>{{ $bahanbaku->satuan }}</td>
                <td>
                    @if($bahanbaku->stok <= $bahanbaku->stok_minimum)
                        <span class="badge bg-danger">Perlu Restock</span>
                        @else
                        <span class="badge bg-success">Aman</span>
                        @endif
                </td>
                <td>
                    <a href="{{ route('bahanbaku.edit', $bahanbaku->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('bahanbaku.destroy', $bahanbaku->id) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
