@extends('layouts.master')

@section('content')
    <div class="max-w-2xl mx-auto p-6 space-y-6">
        {{-- Judul --}}
        <div>
            <h1 class="text-3xl font-bold text-purple-700 flex items-center gap-2">
                <i class="fa fa-plus-circle"></i> Tambah Pemesanan Produk Jadi
            </h1>
            <p class="text-gray-500 text-sm">Isi form untuk melakukan pemesanan produk jadi.</p>
        </div>

        {{-- Error --}}
        @if ($errors->any())
            <div class="alert alert-error shadow-sm">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('pemesanan.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="form-control">
                <label for="produk_id" class="label font-semibold">Pilih Produk</label>
                <select name="produk_id" id="produk_id" class="select select-bordered w-full" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach ($boms as $produk)
                        @php
                            $totalPerProduk = $produk->details->sum('jumlah_per_produk');
                        @endphp
                        <option value="{{ $produk->id }}" data-nama="{{ $produk->produk->nama ?? '-' }}"
                            data-total="{{ $totalPerProduk }}" {{ old('produk_id') == $produk->id ? 'selected' : '' }}>
                            {{ $produk->produk->nama ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-control">
                <label for="jumlah" class="label font-semibold">Jumlah Produk (pcs)</label>
                <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah') ?? 1 }}" min="1"
                    class="input input-bordered w-full" required>
            </div>

            {{-- Ringkasan --}}
            <div class="bg-base-200 p-4 rounded-lg shadow">
                <h2 class="font-semibold mb-2"> Ringkasan Kebutuhan</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full text-sm" id="ringkasanTable">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th class="text-center">Jumlah Produk (pcs)</th>
                                <th class="text-center">Total Bahan Baku (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3" class="text-center text-gray-500">Pilih produk terlebih dahulu</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Kode Pemesanan Otomatis --}}
            <div class="bg-base-100 border p-4 rounded-lg shadow">
                <h2 class="font-semibold mb-2 text-purple-700 flex items-center gap-2">
                    <i class="fa fa-barcode"></i> Kode Produk
                </h2>
                <p class="mt-2 font-bold text-lg text-purple-800" id="kodePreview">-</p>
            </div>

            {{-- Aksi --}}
            <div class="flex gap-4">
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-save"></i> Simpan
                </button>
                <a href="{{ route('pemesanan.dashboard') }}" class="btn btn-ghost text-gray-500 hover:text-purple-600">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const produkSelect = document.getElementById('produk_id');
            const jumlahInput = document.getElementById('jumlah');
            const tbody = document.querySelector('#ringkasanTable tbody');
            const kodePreview = document.getElementById('kodePreview');

            function updateRingkasan() {
                const selected = produkSelect.options[produkSelect.selectedIndex];
                const namaProduk = selected.dataset.nama || '-';
                const totalPerProduk = parseFloat(selected.dataset.total) || 0;
                const jumlah = parseInt(jumlahInput.value) || 0;

                if (!selected.value || jumlah < 1) {
                    tbody.innerHTML =
                        `<tr><td colspan="3" class="text-center text-gray-500 py-2">Pilih produk terlebih dahulu</td></tr>`;
                    return;
                }

                const totalBahanBaku = (totalPerProduk * jumlah / 1000).toFixed(2).replace('.', ',');

                const row = `
                    <tr>
                        <td>${namaProduk}</td>
                        <td class="text-center">${jumlah}</td>
                        <td class="text-center">${totalBahanBaku} kg</td>
                    </tr>
                `;

                tbody.innerHTML = row;
            }

            function generateKodeDOE() {
                const selected = produkSelect.options[produkSelect.selectedIndex];

                if (!selected.value) {
                    kodePreview.textContent = '-';
                    return;
                }

                fetch("{{ route('pemesanan.generateKode') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        },
                        body: JSON.stringify({
                            produk_id: selected.value
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        kodePreview.textContent = data.kode;
                    })
                    .catch(error => {
                        console.error('Gagal mengambil kode:', error);
                        kodePreview.textContent = '-';
                    });
            }

            produkSelect.addEventListener('change', () => {
                updateRingkasan();
                generateKodeDOE();
            });

            jumlahInput.addEventListener('input', () => {
                updateRingkasan();
                generateKodeDOE();
            });

            updateRingkasan();
            generateKodeDOE();
        });
    </script>
@endpush
