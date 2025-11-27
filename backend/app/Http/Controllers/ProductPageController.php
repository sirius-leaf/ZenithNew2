<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;

class ProductPageController extends Controller
{
    /**
     * Menampilkan halaman daftar semua produk (Halaman 'Toko' atau Homepage).
     */
    public function index()
    {
        $products = Product::with('variant')
            ->latest()
            ->paginate(12);

        // UBAH INI:
        // return view('shop.index', compact('products'));

        // MENJADI INI:
        return $products;
    }

    /**
     * Menampilkan halaman detail untuk satu produk.
     * Halaman ini akan menampilkan semua variannya.
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

        // UBAH INI:
        // return view('shop.show', compact('product'));

        // MENJADI INI:
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
