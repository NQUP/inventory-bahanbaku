@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Edit Bahan Baku</h1>
    <form action="{{ route('bahanbaku.update', $bahanbaku->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ $bahanbaku->nama }}" required>
        </div>
        <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" value="{{ $bahanbaku->stok }}" required>
        </div>
        <div class="mb-3">
            <label>Stok Minimum</label>
            <input type="number" name="stok_minimum" class="form-control" value="{{ $bahanbaku->stok_minimum }}" required>
        </div>
        <div class="mb-3">
            <label>Satuan</label>
            <input type="text" name="satuan" class="form-control" value="{{ $bahanbaku->satuan }}" required>
        </div>
        <div class="mb-3">
            <label>Supplier</label>
            <select name="supplier_id" class="form-control" required>
                <option value="">-- Pilih Supplier --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ (old('supplier_id', $bahanbaku->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
