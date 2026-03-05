@extends('layouts.master')

@section('content')
    <div class="container">
        <h3 class="mb-4">Edit Pemesanan</h3>

        {{-- Menampilkan pesan error --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Edit --}}
        <form action="{{ route('pemesanan.update', $pemesanan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="produk_id" class="form-label">Produk</label>
                <select name="produk_id" id="produk_id" class="form-select" required>
                    @foreach ($boms as $bom)
                        <option value="{{ $bom->id }}" {{ $bom->id == $pemesanan->produk_id ? 'selected' : '' }}>
                            {{ $bom->nama_produk ?? 'Produk #' . $bom->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control" value="{{ $pemesanan->jumlah }}"
                    required>
            </div>

            <a href="{{ route('pemesanan.dashboard') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
@endsection
