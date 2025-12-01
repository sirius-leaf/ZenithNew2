<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;

class ProductPageController extends Controller
{
    /**
     * Menampilkan halaman daftar semua produk (Halaman 'Toko' atau Homepage).
     *
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Get all products",
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::with(['variant', 'toko', 'categoryDetail.category'])->latest();

        if ($request->has('category')) {
            $categoryName = $request->input('category');
            $query->whereHas('categoryDetail.category', function ($q) use ($categoryName) {
                $q->where('nama_kategori', $categoryName);
            });
        }

        // Filter by search query (q)
        if ($request->has('q')) {
            $search = $request->input('q');
            $query->where('nama_produk', 'like', "%{$search}%");
        }

        $products = $query->paginate($request->input('per_page', 12));

        // UBAH INI:
        // return view('shop.index', compact('products'));

        // MENJADI INI:
        return $products;
    }

    /**
     * Menampilkan halaman detail untuk satu produk.
     * Halaman ini akan menampilkan semua variannya.
     *
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Get product details",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
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
