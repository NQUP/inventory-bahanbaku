// app/Http/Controllers/OrderController.php
<?php
use App\Models\Order;
use App\Models\BOM;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

public function store(Request $request)
{
    $request->validate([
        'bom_id' => 'required|exists:b_o_m_s,id',
        'jumlah_produk' => 'required|integer|min:1',
    ]);

    DB::transaction(function () use ($request) {
        $order = Order::create([
            'bom_id' => $request->bom_id,
            'jumlah_produk' => $request->jumlah_produk,
        ]);

        $bom = BOM::with('details.bahanBaku')->findOrFail($request->bom_id);

        foreach ($bom->details as $detail) {
            $totalKebutuhan = $detail->jumlah_per_produk * $request->jumlah_produk;

            $bahanBaku = $detail->bahanBaku;
            $bahanBaku->stok -= $totalKebutuhan;
            $bahanBaku->save();
        }
    });

    return redirect()->route('orders.index')->with('success', 'Pemesanan berhasil dan stok bahan baku otomatis dikurangi.');
}
