<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductPageController extends Controller
{
    /**
     * Menampilkan halaman daftar semua produk (Halaman 'Toko' atau Homepage).
     */
    public function index()
    {
        // Ambil semua produk, urutkan dari yang terbaru
        // 'variant' adalah nama relasi di Model Product Anda
        // Kita eager-load 'variant' agar bisa ambil gambar/harga
        $products = Product::with('variant')
                            ->latest()
                            ->paginate(12); // Paginasi 12 produk per halaman

        return view('shop.index', compact('products'));
    }

    /**
     * Menampilkan halaman detail untuk satu produk.
     * Halaman ini akan menampilkan semua variannya.
     */
    public function show($id_produk) // Terima id_produk
    {
        // Cari produk berdasarkan id_produk,
        // ambil juga relasi 'variant'
        $product = Product::with('variant')
                            ->findOrFail($id_produk);

        return view('shop.show', compact('product'));
    }
}
