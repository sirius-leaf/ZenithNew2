<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        if (Auth::user()->role === 'penjual') {
            $products = Auth::user()
                ->toko
                ->with('products')
                ->get();
            //$products = $toko->products->get();
        } else
            $products = [];
        //$products = Product::with(['variant', 'categoryDetail.category'])->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Show the form for creating a new product (not needed for API, but kept if needed)
     */
    public function create()
    {
        $categories = Category::all();
        $store = Auth::user()->toko;

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'store' => $store
            ]
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_produk' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'merek' => 'required|string|max:255',
                'kategori' => 'array|required',
                'kategori.*' => 'exists:categories,id_kategori',
                'varian' => 'required|array|min:1',
                'varian.*.nama_varian' => 'required|string|max:255',
                'varian.*.harga' => 'required|numeric|min:0',
                'varian.*.stok' => 'required|integer|min:0',
                'varian.*.gambar_varian' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $product = DB::transaction(function () use ($validated, $request) {
                $product = Product::create([
                    'id_toko' => Auth::user()->toko->id,
                    'nama_produk' => $validated['nama_produk'],
                    'deskripsi' => $validated['deskripsi'] ?? '',
                    'merek' => $validated['merek'],
                ]);

                foreach ($validated['kategori'] as $k) {
                    $product->categoryDetail()->create([
                        'id_kategori' => $k
                    ]);
                }

                foreach ($validated['varian'] as $i => $varianData) {
                    $path = $request->file("varian.$i.gambar_varian")->store('varians', 'public');

                    $product->variant()->create([
                        'nama_varian' => $varianData['nama_varian'],
                        'harga' => $varianData['harga'],
                        'stok' => $varianData['stok'],
                        'gambar_varian' => $path,
                    ]);
                }

                return $product->load(['variant', 'categoryDetail']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Produk dan variannya berhasil disimpan!',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(int $id)
    {
        $product = Product::with(['variant', 'categoryDetail'])->findOrFail($id);
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'categories' => $categories,
                'store' => Auth::user()->toko
            ]
        ]);
    }

    /**
     * Show product for editing (optional in API, but useful for prefill)
     */
    public function edit(int $id)
    {
        $product = Product::with(['variant', 'categoryDetail'])->findOrFail($id);
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'categories' => $categories,
                'store' => Auth::user()->toko
            ]
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        try {
            $validated = $request->validate([
                'nama_produk' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'merek' => 'required|string|max:255',
                'detail' => 'array',
                'detail.*.id' => 'nullable|exists:category_details,id',
                'detail.*.kategori' => 'required|exists:categories,id_kategori',
                'varian' => 'required|array|min:1',
                'varian.*.id_varian' => 'nullable|exists:variants,id_varian',
                'varian.*.nama_varian' => 'required|string|max:255',
                'varian.*.harga' => 'required|numeric|min:0',
                'varian.*.stok' => 'required|integer|min:0',
                'varian.*.gambar_varian' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($product, $request) {
                // Update product info
                $product->update($request->only('nama_produk', 'merek', 'deskripsi'));

                // --- Handle Categories ---
                $existingDetailIds = $product->categoryDetail->pluck('id')->toArray();
                $incomingDetailIds = collect($request->detail)->pluck('id')->filter()->toArray();

                // Delete removed categories
                $toDeleteCategory = array_diff($existingDetailIds, $incomingDetailIds);
                if (!empty($toDeleteCategory)) {
                    $product->categoryDetail()->whereIn('id', $toDeleteCategory)->delete();
                }

                // Update or create categories
                foreach ($request->detail as $d) {
                    if (!empty($d['id'])) {
                        $detail = $product->categoryDetail()->find($d['id']);
                        if ($detail) {
                            $detail->update(['id_kategori' => $d['kategori']]);
                        }
                    } else {
                        $product->categoryDetail()->create(['id_kategori' => $d['kategori']]);
                    }
                }

                // --- Handle Variants ---
                $existingVariantIds = $product->variant->pluck('id_varian')->toArray();
                $incomingVariantIds = collect($request->varian)->pluck('id_varian')->filter()->toArray();
                $toDeleteVariant = array_diff($existingVariantIds, $incomingVariantIds);

                // Delete removed variants + images
                foreach ($toDeleteVariant as $vId) {
                    $variant = $product->variant()->find($vId);
                    if ($variant && $variant->gambar_varian) {
                        Storage::disk('public')->delete($variant->gambar_varian);
                    }
                }
                if (!empty($toDeleteVariant)) {
                    $product->variant()->whereIn('id_varian', $toDeleteVariant)->delete();
                }

                // Update or create variants
                foreach ($request->varian as $i => $v) {
                    if (!empty($v['id_varian'])) {
                        $variant = $product->variant()->find($v['id_varian']);
                        if ($variant) {
                            $path = $variant->gambar_varian;
                            if ($request->hasFile("varian.$i.gambar_varian")) {
                                // Delete old image
                                Storage::disk('public')->delete($path);
                                // Store new
                                $path = $request->file("varian.$i.gambar_varian")->store('varians', 'public');
                            }
                            $variant->update([
                                'nama_varian' => $v['nama_varian'],
                                'harga' => $v['harga'],
                                'stok' => $v['stok'],
                                'gambar_varian' => $path,
                            ]);
                        }
                    } else {
                        $path = '';
                        if ($request->hasFile("varian.$i.gambar_varian")) {
                            $path = $request->file("varian.$i.gambar_varian")->store('varians', 'public');
                        }
                        $product->variant()->create([
                            'nama_varian' => $v['nama_varian'],
                            'harga' => $v['harga'],
                            'stok' => $v['stok'],
                            'gambar_varian' => $path,
                        ]);
                    }
                }
            });

            $updatedProduct = $product->fresh()->load(['variant', 'categoryDetail']);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui!',
                'data' => $updatedProduct
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(int $id)
    {
        $product = Product::with(['variant', 'categoryDetail'])->findOrFail($id);

        try {
            DB::transaction(function () use ($product) {
                // Delete category links
                $product->categoryDetail()->delete();

                // Delete variants + images
                foreach ($product->variant as $variant) {
                    if ($variant->gambar_varian) {
                        Storage::disk('public')->delete($variant->gambar_varian);
                    }
                    $variant->delete();
                }

                $product->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}