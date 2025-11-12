<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Variant;
use App\Models\Pesanan;
use App\Models\Toko;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /**
     * Asumsi Keranjang (Cart):
     * Kita asumsikan Anda menyimpan keranjang di session dengan format:
     * session('cart') = [
     * 'id_varian_1' => ['kuantitas' => 2],
     * 'id_varian_5' => ['kuantitas' => 1],
     * ];
     */

    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        // Ambil data keranjang untuk ditampilkan sebagai ringkasan
        $cartItems = [];
        $totalPrice = 0;
        $variantIds = array_keys($cart);
        $variants = Variant::whereIn('id_varian', $variantIds)->get();

        foreach ($variants as $variant) {
            $kuantitas = $cart[$variant->id_varian]['kuantitas'];
            $cartItems[] = [
                'nama' => $variant->product->nama_produk . ' (' . $variant->nama_varian . ')',
                'kuantitas' => $kuantitas
            ];
            $totalPrice += $variant->harga * $kuantitas;
        }

        return view('checkout.index', compact('cartItems', 'totalPrice'));
    }

    public function store(Request $request)
    {
        // 1. Validasi input (minimal alamat)
        $validated = $request->validate([
            'alamat_pengiriman' => 'required|string|max:500',
            // Tambahkan validasi lain jika perlu (metode pembayaran, dll)
        ]);

        $cart = session('cart');
        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Keranjang Anda kosong.');
        }

        $user = Auth::user();
        $itemsPerToko = []; // Untuk mengelompokkan item per toko

        // 2. Ambil semua data varian dari DB sekaligus (lebih efisien)
        // Kita butuh 'product.toko' untuk tahu ID tokonya
        $variantIds = array_keys($cart);
        $variants = Variant::with('product.toko')
            ->whereIn('id_varian', $variantIds)
            ->get();

        // 3. Cek Stok & Kelompokkan per Toko
        foreach ($variants as $variant) {
            $kuantitasDipesan = $cart[$variant->id_varian]['kuantitas'];

            // Cek stok dulu
            if ($variant->stok < $kuantitasDipesan) {
                // Jika stok tidak cukup, batalkan
                return redirect()->back()->with('error', 'Stok untuk ' . $variant->nama_varian . ' tidak mencukupi.');
            }

            // Dapatkan ID Toko
            $tokoId = $variant->product->toko->id;

            // Masukkan ke grup toko
            $itemsPerToko[$tokoId][] = [
                'variant' => $variant,
                'kuantitas' => $kuantitasDipesan
            ];
        }

        // 4. Gunakan DB Transaction (SANGAT PENTING)
        // Jika satu pesanan gagal, semua dibatalkan.
        try {
            DB::transaction(function () use ($itemsPerToko, $user, $validated) {

                // 5. Buat Pesanan terpisah untuk SETIAP TOKO
                foreach ($itemsPerToko as $tokoId => $items) {

                    $totalHargaPesanan = 0;

                    // Hitung total harga untuk pesanan di toko ini
                    foreach ($items as $item) {
                        $totalHargaPesanan += $item['variant']->harga * $item['kuantitas'];
                    }

                    // A. Buat entri di tabel 'pesanans'
                    $pesanan = Pesanan::create([
                        'user_id' => $user->id,
                        'toko_id' => $tokoId,
                        'total_harga' => $totalHargaPesanan,
                        'status' => 'pending', // Status awal
                        'alamat_pengiriman' => $validated['alamat_pengiriman']
                    ]);

                    // B. Buat entri di 'detail_pesanans' & Update Stok
                    foreach ($items as $item) {
                        $variant = $item['variant'];
                        $kuantitas = $item['kuantitas'];

                        // Buat detail pesanan
                        $pesanan->detailPesanans()->create([
                            'id_varian' => $variant->id_varian, // Pakai id_varian
                            'kuantitas' => $kuantitas,
                            'harga' => $variant->harga // 'Snapshot' harga saat ini
                        ]);

                        // Kurangi stok (gunakan 'decrement' agar aman dari race condition)
                        $variant->decrement('stok', $kuantitas);
                    }
                }
            });
        } catch (\Exception $e) {
            // Jika ada error (misal stok tiba-tiba habis), batalkan
            return redirect()->back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }

        // 6. Jika semua sukses, kosongkan keranjang & redirect
        session()->forget('cart');

        return redirect()->route('checkout.success')->with('success', 'Pesanan Anda berhasil dibuat!');
    }

    /**
     * Halaman 'Sukses' (Opsional)
     */
    public function success()
    {
        // Pastikan halaman ini hanya bisa diakses setelah 'success'
        if (!session('success')) {
            return redirect()->route('home');
        }
        return view('checkout.success'); // Buat view ini
    }
}
