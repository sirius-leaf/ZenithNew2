<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PcBuild;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PcBuildController extends Controller
{
    /**
     * GET /api/pc-build
     * List semua build milik user login
     */
    public function index()
    {
        $pcBuild = Auth::user()
            ->pcBuild()
            ->with('buildDetail.variant.product')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pcBuild
        ]);
    }

    /**
     * GET /api/pc-build/products
     * Ambil data produk untuk form (motherboard, cpu, dll)
     */
    public function products()
    {
        $products = Product::with('variant', 'toko', 'categoryDetail.category')->get();
        $variants = Variant::all();

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'variants' => $variants,
        ]);
    }

    /**
     * POST /api/pc-build
     * Simpan build baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_build' => 'required',
            'komponen' => 'required|array',
            'komponen.motherboard' => 'nullable',
            'komponen.cpu' => 'nullable',
            'komponen.ram' => 'nullable',
            'komponen.psu' => 'nullable',
            'komponen.storage' => 'nullable',
            'komponen.cooler' => 'nullable',
            'komponen.video-card' => 'nullable',
            'komponen.case' => 'nullable',
            'komponen.monitor' => 'nullable',
            'komponen.mouse' => 'nullable',
            'komponen.keyboard' => 'nullable',
        ]);

        $build = PcBuild::create([
            'id_user' => Auth::id(),
            'nama_build' => $validated['nama_build'],
        ]);

        foreach ($validated['komponen'] as $bagian => $produk) {
            if ($produk) { // Only create if product is selected
                $build->buildDetail()->create([
                    'id_varian' => $produk,
                    'bagian_komponen' => $bagian,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'PC build berhasil disimpan!',
            'data' => $build->load('buildDetail.variant')
        ]);
    }

    /**
     * GET /api/pc-build/{id}
     * Detail satu build
     */
    public function show($id)
    {
        $build = PcBuild::with('buildDetail.variant.product')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $build
        ]);
    }

    /**
     * PUT /api/pc-build/{id}
     * Update build
     */
    public function update(Request $request, $id)
    {
        $build = PcBuild::findOrFail($id);

        $validated = $request->validate([
            'nama_build' => 'required',
            'komponen' => 'required|array',
            'komponen.*.id' => 'nullable|numeric', // Detail ID might be null if adding new component
            'komponen.*.produk' => 'nullable|numeric', // Product ID can be null if removing
        ]);

        foreach ($validated['komponen'] as $bagian => $item) {
            // Check if we have an existing detail ID
            if (isset($item['id']) && $item['id']) {
                $detail = $build->buildDetail()->find($item['id']);
                if ($detail) {
                    if ($item['produk']) {
                        // Update existing component
                        $detail->update([
                            'id_varian' => $item['produk'],
                            'bagian_komponen' => $bagian,
                        ]);
                    } else {
                        // Remove component if product is null
                        $detail->delete();
                    }
                }
            } else {
                // No existing detail ID, check if we need to create one
                if (isset($item['produk']) && $item['produk']) {
                    $build->buildDetail()->create([
                        'id_varian' => $item['produk'],
                        'bagian_komponen' => $bagian,
                    ]);
                }
            }
        }

        $build->update([
            'nama_build' => $validated['nama_build']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'PC build berhasil diperbarui!',
            'data' => $build->load('buildDetail.variant')
        ]);
    }

    /**
     * DELETE /api/pc-build/{id}
     */
    public function destroy($id)
    {
        $build = PcBuild::with('buildDetail')->findOrFail($id);

        // Hapus detail komponen
        foreach ($build->buildDetail as $detail) {
            $detail->delete();
        }

        // Hapus build utama
        $build->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'PC build dihapus!'
        ]);
    }
}
