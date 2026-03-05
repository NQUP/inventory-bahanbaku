@extends('layouts.master')

@section('content')
    <div class="container">
        <h2>Hasil Konversi Bahan Baku</h2>
        <p><strong>Produk:</strong> {{ $produk->nama }}</p>
        <p><strong>Jumlah Dipesan:</strong> {{ $jumlah_pesanan }}</p>

        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Bahan Baku</th>
                    <th>Kebutuhan Total</th>
                    <th>Satuan</th>
                    <th>Konversi ke Satuan Pembelian</th>
                    <th>Satuan Pembelian</th>
                    <th>Stok Saat Ini</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detailBahan as $bahan)
                    <tr>
                        <td>{{ $bahan['nama'] }}</td>
                        <td>{{ $bahan['kebutuhan_total'] }}</td>
                        <td>{{ $bahan['satuan'] }}</td>
                        <td>{{ $bahan['jumlah_dalam_satuan_pembelian'] }}</td>
                        <td>{{ $bahan['satuan_pembelian'] }}</td>
                        <td>{{ $bahan['stok_saat_ini'] }}</td>
                        <td style="color: {{ $bahan['cukup'] ? 'green' : 'red' }}">
                            {{ $bahan['cukup'] ? ' Cukup' : ' Tidak Cukup' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('pemesanan.create') }}" class="btn btn-secondary mt-3">Kembali ke Form Pemesanan</a>
    </div>
@endsection
