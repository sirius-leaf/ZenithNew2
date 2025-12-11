<?php

namespace CodeTests\SingleModul;

use App\Models\Product;
use App\Models\DetailPesanan;

class ProductDetail
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
}
