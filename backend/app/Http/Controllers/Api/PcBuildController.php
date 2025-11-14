<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PcBuild;
use App\Models\Product;
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
            ->with('buildDetail.product')
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
        $products = Product::all();

        return response()->json([
            'status' => 'success',
            'data' => $products
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
            'komponen.motherboard' => 'required',
            'komponen.cpu' => 'required',
            'komponen.ram' => 'required',
            'komponen.psu' => 'required',
            'komponen.storage' => 'required',
        ]);

        $build = PcBuild::create([
            'id_user' => Auth::id(),
            'nama_build' => $validated['nama_build'],
        ]);

        foreach ($validated['komponen'] as $bagian => $produk) {
            $build->buildDetail()->create([
                'id_produk' => $produk,
                'bagian_komponen' => $bagian,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'PC build berhasil disimpan!',
            'data' => $build->load('buildDetail.product')
        ]);
    }

    /**
     * GET /api/pc-build/{id}
     * Detail satu build
     */
    public function show($id)
    {
        $build = PcBuild::with('buildDetail.product')->findOrFail($id);

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
            'komponen.*.id' => 'required|numeric',
            'komponen.*.produk' => 'required|numeric',
        ]);

        foreach ($validated['komponen'] as $bagian => $item) {
            $detail = $build->buildDetail()->find($item['id']);
            if ($detail) {
                $detail->update([
                    'id_produk' => $item['produk'],
                    'bagian_komponen' => $bagian,
                ]);
            }
        }

        $build->update([
            'nama_build' => $validated['nama_build']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'PC build berhasil diperbarui!',
            'data' => $build->load('buildDetail.product')
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
