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
        $product = Product::with('variant')
                            ->findOrFail($id_produk);

        // UBAH INI:
        // return view('shop.show', compact('product'));

        // MENJADI INI:
        return $product;
    }
}
