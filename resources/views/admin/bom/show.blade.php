@extends('layouts.master')

@section('content')
    <div class="container">
        <h1 class="mb-4">Detail Bill of Materials (BOM)</h1>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Produk Jadi</h4>
                <p><strong>Nama Produk:</strong> {{ $bom->nama_produk }}</p>

                <h5 class="mt-4">Daftar Bahan Baku</h5>
                <table class="table table-bordered mt-2">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Bahan Baku</th>
                            <th>Jumlah per Produk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bom->details as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->bahanBaku->nama_bahan }}</td>
                                <td>{{ $detail->jumlah_per_produk }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <a href="{{ route('admin.bom.index') }}" class="btn btn-secondary mt-3">Kembali</a>
                <a href="{{ route('admin.bom.edit', $bom->id) }}" class="btn btn-warning mt-3">Edit</a>
            </div>
        </div>
    </div>
@endsection
