<h2>Statistik Pemesanan per Bulan ({{ date('Y') }})</h2>

@if($stats->isEmpty())
<p>Belum ada data pemesanan.</p>
@else
<ul>
    @foreach ($stats as $stat)
    @php
    $bulanNama = DateTime::createFromFormat('!m', $stat->bulan)->format('F');
    @endphp
    <li>Bulan {{ $bulanNama }}: {{ $stat->total_pemesanan }} pemesanan</li>
    @endforeach
</ul>
@endif