<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicProductController extends Controller
{
    public function getCategories()
    {
        $categories = Category::select('id_kategori as id', 'nama_kategori as name')->get();
        return response()->json($categories);
    }

    public function recommended()
    {
        $products = Product::with([
            'variants' => fn($q) => $q->where('stok', '>', 0)->orderBy('harga'),
            'toko'
        ])
        ->whereHas('variants', fn($q) => $q->where('stok', '>', 0))
        ->inRandomOrder()
        ->take(6)
        ->get()
        ->map(function ($product) {
            $cheapest = $product->variants->first();
            return [
                'id' => $product->id_produk,
                'nama_produk' => $product->nama_produk,
                'merek' => $product->merek,
                'deskripsi' => Str::limit($product->deskripsi ?? '', 80),
                'harga' => floatval($cheapest->harga),
                'stok' => $cheapest->stok,
                'gambar' => $cheapest->gambar_varian 
                    ? asset('storage/' . $cheapest->gambar_varian) 
                    : '/img_placeholder.jpg',
                'toko' => $product->toko?->toko_name ?? '—',
            ];
        });

        return response()->json($products);
    }
}