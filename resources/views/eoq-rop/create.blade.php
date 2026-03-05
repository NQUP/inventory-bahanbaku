@extends('layouts.master')

@section('title', 'Input EOQ & ROP')

@section('content')
@php
    $formAction = auth()->user()->hasRole('admin')
        ? route('admin.eoq-rop.store')
        : route('manager.eoq-rop.store');

    $stokSaatIni = (float) ($bahanBaku->stok ?? 0);
@endphp

<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-2">
        <h1 class="app-title">Perhitungan EOQ & ROP</h1>
        <p class="app-subtitle">Hitung kuantitas pemesanan optimal dan titik pemesanan ulang untuk bahan baku.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4">
                <p class="text-xs text-slate-500">Bahan Baku</p>
                <p class="text-lg font-bold text-slate-900">{{ $bahanBaku->nama }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4">
                <p class="text-xs text-slate-500">Stok Saat Ini</p>
                <p class="text-lg font-bold text-slate-900">{{ number_format($stokSaatIni, 2, ',', '.') }} {{ $bahanBaku->satuan ?? '' }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4">
                <p class="text-xs text-slate-500">EOQ/ROP Terakhir</p>
                <p class="text-sm font-semibold text-slate-700">
                    EOQ: {{ number_format((float) ($parameter->eoq ?? 0), 2, ',', '.') }} | ROP: {{ number_format((float) ($parameter->rop ?? 0), 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">1) Input Parameter</h2>
                <form action="{{ $formAction }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2" id="eoqRopForm">
                    @csrf
                    <input type="hidden" name="bahan_baku_id" value="{{ $bahanBaku->id }}">

                    <div>
                        <label class="label"><span class="label-text font-semibold">Permintaan Tahunan (D)</span></label>
                        <input id="demand_tahunan" type="number" step="0.01" min="1" name="demand_tahunan" class="input input-bordered w-full"
                            value="{{ old('demand_tahunan', $parameter->demand_tahunan ?? '') }}" required>
                    </div>

                    <div>
                        <label class="label"><span class="label-text font-semibold">Biaya Pemesanan (S)</span></label>
                        <input id="biaya_pemesanan" type="number" step="0.01" min="0" name="biaya_pemesanan" class="input input-bordered w-full"
                            value="{{ old('biaya_pemesanan', $parameter->biaya_pemesanan ?? '') }}" required>
                    </div>

                    <div>
                        <label class="label"><span class="label-text font-semibold">Biaya Penyimpanan (H)</span></label>
                        <input id="biaya_penyimpanan" type="number" step="0.01" min="0.01" name="biaya_penyimpanan" class="input input-bordered w-full"
                            value="{{ old('biaya_penyimpanan', $parameter->biaya_penyimpanan ?? '') }}" required>
                    </div>

                    <div>
                        <label class="label"><span class="label-text font-semibold">Lead Time (hari)</span></label>
                        <input id="lead_time" type="number" step="0.01" min="0" name="lead_time" class="input input-bordered w-full"
                            value="{{ old('lead_time', $parameter->lead_time ?? '') }}" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Safety Stock (opsional)</span></label>
                        <input id="safety_stock" type="number" step="0.01" min="0" name="safety_stock" class="input input-bordered w-full"
                            value="{{ old('safety_stock', 0) }}">
                        <p class="mt-1 text-xs text-slate-500">Jika tidak diisi, sistem gunakan nilai 0.</p>
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="btn btn-primary">Simpan Parameter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">2) Hasil Otomatis</h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-slate-500">EOQ</p>
                            <p id="preview-eoq" class="text-xl font-bold text-slate-900">0.00</p>
                        </div>
                        <div>
                            <p class="text-slate-500">ROP</p>
                            <p id="preview-rop" class="text-xl font-bold text-slate-900">0.00</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Daily Demand</p>
                            <p id="preview-daily" class="font-semibold text-slate-700">0.00 / hari</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">3) Rekomendasi Aksi</h2>
                    <div id="reorder-badge" class="badge badge-success">Stok Aman</div>
                    <p id="reorder-note" class="text-sm text-slate-600">Stok saat ini masih di atas titik ROP.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body text-sm text-slate-600">
            <p class="font-semibold text-slate-800">Rumus yang digunakan</p>
            <p>EOQ = sqrt((2 * D * S) / H)</p>
            <p>ROP = (D / 365 * Lead Time) + Safety Stock</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const parseNum = (id) => {
        const el = document.getElementById(id);
        if (!el) return 0;
        const val = Number(el.value);
        return Number.isFinite(val) ? val : 0;
    };

    const eoqEl = document.getElementById('preview-eoq');
    const ropEl = document.getElementById('preview-rop');
    const dailyEl = document.getElementById('preview-daily');
    const badgeEl = document.getElementById('reorder-badge');
    const noteEl = document.getElementById('reorder-note');
    const stokSaatIni = {{ json_encode($stokSaatIni) }};

    const format = (num) => num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const render = () => {
        const D = parseNum('demand_tahunan');
        const S = parseNum('biaya_pemesanan');
        const H = parseNum('biaya_penyimpanan');
        const L = parseNum('lead_time');
        const ss = parseNum('safety_stock');

        const dailyDemand = D > 0 ? D / 365 : 0;
        const eoq = D > 0 && S >= 0 && H > 0 ? Math.sqrt((2 * D * S) / H) : 0;
        const rop = (dailyDemand * L) + ss;

        eoqEl.textContent = format(eoq);
        ropEl.textContent = format(rop);
        dailyEl.textContent = `${format(dailyDemand)} / hari`;

        if (stokSaatIni <= rop && rop > 0) {
            badgeEl.className = 'badge badge-error';
            badgeEl.textContent = 'Perlu Reorder';
            noteEl.textContent = 'Stok saat ini sudah menyentuh atau di bawah ROP. Disarankan buat pemesanan ulang.';
        } else {
            badgeEl.className = 'badge badge-success';
            badgeEl.textContent = 'Stok Aman';
            noteEl.textContent = 'Stok saat ini masih di atas titik ROP.';
        }
    };

    ['demand_tahunan', 'biaya_pemesanan', 'biaya_penyimpanan', 'lead_time', 'safety_stock'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', render);
    });

    render();
})();
</script>
@endpush
