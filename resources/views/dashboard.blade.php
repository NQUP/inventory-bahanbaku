<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Monitoring') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @php
            $role = auth()->user()->role; // sesuaikan dengan kolom role di user tabel
            @endphp

            {{-- Dashboard Admin, Manager, Gudang --}}
            @if(in_array($role, ['admin', 'manager', 'gudang']))
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="font-bold text-gray-700 mb-2">Total Bahan Baku</div>
                    <div class="text-3xl font-semibold">{{ $totalBahanBaku ?? 0 }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="font-bold text-gray-700 mb-2">Pemesanan Aktif</div>
                    <div class="text-3xl font-semibold">{{ $totalPemesanan ?? 0 }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="font-bold text-gray-700 mb-2">Pemesanan Pending</div>
                    <div class="text-3xl font-semibold">{{ $pemesananPending ?? 0 }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="font-bold text-gray-700 mb-2">Pemesanan Diterima</div>
                    <div class="text-3xl font-semibold">{{ $pemesananDiterima ?? 0 }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h4 class="text-lg font-bold mb-4">Statistik Pemesanan per Bulan ({{ now()->year }})</h4>
                <canvas id="pemesananChart" height="100"></canvas>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h4 class="text-lg font-bold mb-4">Stok Menipis</h4>
                @if(empty($stokMenipis) || $stokMenipis->isEmpty())
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    Tidak ada stok menipis
                </div>
                @else
                <ul class="list-disc pl-6">
                    @foreach($stokMenipis as $item)
                    <li class="mb-2">
                        {{ $item->nama }} ({{ $item->stok }} {{ $item->satuan }})
                        <span class="ml-2 inline-block bg-red-600 text-white text-xs px-2 py-1 rounded">Perlu Restock</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

            {{-- Dashboard Supplier --}}
            @elseif($role === 'supplier')
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4">Dashboard Supplier</h3>
                <p>Jumlah Permintaan Masuk: {{ $jumlahPermintaan ?? 0 }}</p>
                <p>Permintaan yang sudah dikonfirmasi: {{ $permintaanDikonfirmasi ?? 0 }}</p>
                {{-- Tambahkan konten sesuai kebutuhan supplier --}}
            </div>

            {{-- Dashboard Pemesan --}}
            @elseif($role === 'pemesan')
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4">Dashboard Pemesan</h3>
                <p>Jumlah Pemesanan Anda: {{ $jumlahPemesananPemesan ?? 0 }}</p>
                <p>Status Pemesanan Terbaru: {{ $statusPemesananTerbaru ?? 'Tidak ada data' }}</p>
                {{-- Tambahkan konten sesuai kebutuhan pemesan --}}
            </div>

            {{-- Role tidak dikenali --}}
            @else
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p>Dashboard belum tersedia untuk role Anda.</p>
            </div>
            @endif

        </div>
    </div>

    @if(in_array($role, ['admin', 'manager', 'gudang']))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('pemesananChart').getContext('2d');
        const pemesananChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {
                    !!json_encode($labels ?? []) !!
                },
                datasets: [{
                    label: 'Jumlah Pemesanan',
                    data: {
                        !!json_encode($totals ?? []) !!
                    },
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
    @endif

</x-app-layout>