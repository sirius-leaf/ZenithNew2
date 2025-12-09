<?php

namespace CodeTests\GroupedModul;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\DetailPesanan;
use App\Models\Variant;

class ProductGroup
{
    /**
     * Menampilkan halaman detail untuk satu produk.
     * Replika dari ProductPageController::show
     */
    public function show($id_produk)
    {
        $product = Product::with(['variant', 'reviews.user', 'toko'])
            ->findOrFail($id_produk);

        $rating = floor($product->reviews()->avg('rating') * 10) / 10;
        $ratingCount = $product->reviews()->count();

        $totalTerjual = DetailPesanan::whereHas('variant', function ($q) use ($id_produk) {
            $q->where('id_produk', $id_produk);
        })->sum('kuantitas');

        return response()->json([
            'data' => $product,
            'rating' => [
                'rata-rata' => $rating ?? 0,
                'jumlah' => $ratingCount,
                'terjual' => $totalTerjual,
            ],
        ], 201);
    }

    /**
     * Menambahkan item ke keranjang.
     * Replika dari CartController::add
     */
    public function add(Request $request, $id_varian)
    {
        $variant = Variant::findOrFail($id_varian);
        $kuantitas = $request->input('kuantitas', 1); // Ambil kuantitas, default 1

        // Validasi stok
        if ($variant->stok < $kuantitas) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi.');
        }

        $cart = session()->get('cart', []);

        // Cek jika item sudah ada di keranjang, tambahkan kuantitasnya
        if (isset($cart[$id_varian])) {
            $cart[$id_varian]['kuantitas'] += $kuantitas;
        } else {
            // Jika item baru
            $cart[$id_varian] = [
                'kuantitas' => $kuantitas
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }
}
