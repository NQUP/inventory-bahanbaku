@extends('layouts.master')

@section('content')
    <div class="container py-4">
        <h3>Detail Pemesanan</h3>

        <a href="{{ route('supplier.dashboard') }}" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p><strong>Produk / Bahan:</strong> {{ $pemesanan->produk->nama ?? ($pemesanan->bahanBaku->nama ?? '-') }}</p>
        <p><strong>Jumlah:</strong> {{ $pemesanan->jumlah }}</p>
        <p><strong>Status Saat Ini:</strong> {{ $pemesanan->status }}</p>
        <p><strong>Tanggal Pemesanan:</strong> {{ $pemesanan->tanggal?->format('d-m-Y') ?? '-' }}</p>

        <form method="POST" action="{{ route('supplier.pemesanan.updateStatus', $pemesanan->id) }}">
            @csrf
            @method('PUT')
            <label for="status">Ubah Status:</label>
            <select name="status" id="status" class="form-select mb-3" required>
                <option value="Pending" {{ $pemesanan->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Dikirim" {{ $pemesanan->status == 'Dikirim' ? 'selected' : '' }}>Dikirim</option>
                <option value="Selesai" {{ $pemesanan->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
            <button type="submit" class="btn btn-success">Update Status</button>
        </form>
    </div>
@endsection
