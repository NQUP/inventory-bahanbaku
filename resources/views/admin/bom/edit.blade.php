@extends('layouts.master')

@section('content')
    <div class="container">
        <h1 class="mb-4">Edit BOM: {{ $bom->nama_produk }}</h1>

        <form action="{{ route('admin.bom.update', $bom->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="nama_produk">Nama Produk Jadi</label>
                <input type="text" name="nama_produk" id="nama_produk" value="{{ $bom->nama_produk }}" class="form-control"
                    required>
            </div>

            <h5>Bahan Baku</h5>
            <div id="bahan-container">
                @foreach ($bom->details as $detail)
                    <div class="form-row d-flex mb-2">
                        <select name="bahan_baku_id[]" class="form-control me-2" required>
                            <option value="">-- Pilih Bahan Baku --</option>
                            @foreach ($bahanBakus as $bahan)
                                <option value="{{ $bahan->id }}"
                                    {{ $bahan->id == $detail->bahan_baku_id ? 'selected' : '' }}>
                                    {{ $bahan->nama_bahan }}
                                </option>
                            @endforeach
                        </select>
                        <input type="number" name="jumlah_per_produk[]" value="{{ $detail->jumlah_per_produk }}"
                            class="form-control me-2" placeholder="Jumlah" required>
                        <button type="button" class="btn btn-danger btn-sm remove-bahan">×</button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="tambah-bahan" class="btn btn-sm btn-secondary mb-3">+ Tambah Bahan</button>
            <br>
            <button type="submit" class="btn btn-primary">Update BOM</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('tambah-bahan').addEventListener('click', function() {
            let container = document.getElementById('bahan-container');
            let bahanList = `@json($bahanBakus)`;

            let html = `<div class="form-row d-flex mb-2">
            <select name="bahan_baku_id[]" class="form-control me-2" required>
                <option value="">-- Pilih Bahan Baku --</option>
                ${JSON.parse(bahanList).map(b => `<option value="${b.id}">${b.nama_bahan}</option>`).join('')}
            </select>
            <input type="number" name="jumlah_per_produk[]" class="form-control me-2" placeholder="Jumlah" required>
            <button type="button" class="btn btn-danger btn-sm remove-bahan">×</button>
        </div>`;

            container.insertAdjacentHTML('beforeend', html);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-bahan')) {
                e.target.closest('.form-row').remove();
            }
        });
    </script>
@endpush
