<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Pastikan Auth di-import
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MyTokoProductController extends Controller
{
    /**
     * Tampilkan daftar produk (dialihkan ke dashboard toko)
     */
    public function index()
    {
        return redirect()->route('dashboard.toko.show');
    }

    /**
     * Tampilkan form untuk membuat produk baru.
     */
    public function create()
    {
        $kategori = Category::all();
        return view('my_toko.product.create', compact('kategori'));
    }

    /**
     * Simpan produk baru ke toko milik user.
     */
    public function store(Request $request)
    {
        // Ambil toko langsung di dalam method
        $toko = Auth::user()->toko;

        $validated = $request->validate([
            'nama_produk' => 'required',
            'deskripsi' => 'nullable|string',
            'merek' => 'required|string',
            'kategori' => 'array|required',
            'varian.*.nama_varian' => 'required|string',
            'varian.*.harga' => 'required|numeric|min:0',
            'varian.*.stok' => 'required|numeric|min:0',
            'varian.*.gambar_varian' => 'required|file|image|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request, $toko) {
            // Gunakan variabel $toko yang baru diambil
            $produk = $toko->products()->create([
                'nama_produk' => $validated['nama_produk'],
                'deskripsi' => $validated['deskripsi'] ?? '',
                'merek' => $validated['merek'],
            ]);

            // ... (Sisa logika store Anda) ...

            foreach ($validated['kategori'] as $k) {
                $produk->categoryDetail()->create([
                    'id_kategori' => $k
                ]);
            }

            foreach ($validated['varian'] as $i => $varianData) {
                $path = $request->file("varian.$i.gambar_varian")->store('varians', 'public');
                $produk->variant()->create([
                    'nama_varian' => $varianData['nama_varian'],
                    'harga' => $varianData['harga'],
                    'stok' => $varianData['stok'],
                    'gambar_varian' => $path,
                ]);
            }
        });

        return redirect()->route('dashboard.toko.show')
            ->with('success', 'Produk dan variannya berhasil disimpan!');
    }

    /**
     * Tampilkan detail (dialihkan ke dashboard toko)
     */
    public function show(string $id)
    {
        return redirect()->route('dashboard.toko.show');
    }

    /**
     * Tampilkan form untuk edit produk.
     */
    public function edit(string $id)
    {
        // Ambil toko langsung di dalam method
        $toko = Auth::user()->toko;

        $produk = $toko->products()->with(['variant', 'categoryDetail'])->find($id);

        if (!$produk) {
            return redirect()->route('dashboard.toko.show')->with('error', 'Produk tidak ditemukan.');
        }

        $kategori = Category::all();
        return view('my_toko.product.edit', compact('produk', 'kategori'));
    }

    /**
     * Update produk yang ada.
     */
    public function update(Request $request, string $id)
    {
        // Ambil toko langsung di dalam method
        $toko = Auth::user()->toko;

        $produk = $toko->products()->find($id);
        if (!$produk) {
            return redirect()->route('dashboard.toko.show')->with('error', 'Produk tidak ditemukan.');
        }

        // --- (Salin semua logika validasi & update Anda ke sini) ---
        $validated = $request->validate([
            'nama_produk' => 'required',
            'deskripsi' => 'nullable|string',
            'merek' => 'required|string',
            'detail.*.kategori' => 'required',
            'varian.*.nama_varian' => 'required|string',
            'varian.*.harga' => 'required|numeric|min:0',
            'varian.*.stok' => 'required|numeric|min:0',
            'varian.*.gambar_varian' => 'nullable|file|image',
        ]);

        // ... (Logika update kategori) ...
        // ... (Logika update varian + Hapus file) ...
        // (Salin dari kode Anda sebelumnya)

        $produk->update($request->only('nama_produk', 'merek', 'deskripsi'));
        return redirect()->route('dashboard.toko.show')->with('success', 'Produk berhasil diperbarui!');
    }

    /**
     * Hapus produk dari toko.
     */
    public function destroy(string $id)
    {
        // Ambil toko langsung di dalam method
        $toko = Auth::user()->toko;

        $produk = $toko->products()->with('variant', 'categoryDetail')->find($id);
        if (!$produk) {
            return redirect()->route('dashboard.toko.show')->with('error', 'Produk tidak ditemukan.');
        }

        // --- (Salin semua logika hapus Anda ke sini) ---
        foreach ($produk->categoryDetail as $detail) {
            $detail->delete();
        }

        foreach ($produk->variant as $variant) {
            if ($variant->gambar_varian) {
                 Storage::disk('public')->delete($variant->gambar_varian);
            }
            $variant->delete();
        }

        $produk->delete();
        return redirect()->back()->with('success', 'ProduK dihapus!');
    }
}
