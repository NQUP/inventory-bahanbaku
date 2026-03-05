<h3>Kebutuhan Bahan Baku untuk {{ $order->bom->nama_produk }}</h3>

<ul>
    @foreach ($kebutuhan as $item)
        <li>{{ $item['nama_bahan_baku'] }}: {{ $item['jumlah_dibutuhkan'] }} {{ $item['satuan'] }}</li>
    @endforeach
</ul>
