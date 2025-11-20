<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Menerima item keranjang dari frontend dan mengembalikan ringkasan pesanan.
     * Endpoint ini menggantikan CartController@index dan CheckoutController@index.
     * @param Request $request {cartItems: [{id_varian: X, kuantitas: Y}, ...]}
     */
    public function preview(Request $request)
    {
        // 1. Validasi input keranjang dari Vue
        $request->validate([
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id_varian' => 'required|exists:variants,id_varian',
            'cartItems.*.kuantitas' => 'required|integer|min:1',
        ]);

        $cartItemsInput = $request->input('cartItems');
        $variantIds = collect($cartItemsInput)->pluck('id_varian')->toArray();

        // 2. Ambil data varian lengkap dari DB
        $variants = Variant::with('product.toko')
            ->whereIn('id_varian', $variantIds)
            ->get()
            ->keyBy('id_varian'); // Kunci hasil dengan id_varian

        $cartSummary = [];
        $totalPrice = 0;

        // 3. Hitung total harga dan cek stok
        foreach ($cartItemsInput as $item) {
            $variant = $variants->get($item['id_varian']);
            $kuantitas = $item['kuantitas'];

            if (!$variant) {
                // Seharusnya tidak terjadi karena sudah divalidasi
                continue;
            }

            // Cek Stok
            if ($variant->stok < $kuantitas) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi untuk ' . $variant->product->nama_produk . ' (' . $variant->nama_varian . ')',
                    'variant_id' => $variant->id_varian,
                ], 400); // 400 Bad Request
            }

            $subtotal = $variant->harga * $kuantitas;
            $totalPrice += $subtotal;

            $cartSummary[] = [
                'variant' => $variant,
                'kuantitas' => $kuantitas,
                'subtotal' => $subtotal,
            ];
        }

        return response()->json([
            'status' => 'success',
            'cartItems' => $cartSummary,
            'totalPrice' => $totalPrice,
        ], 200);
    }

    /**
     * Memproses pesanan.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi input keranjang dan alamat
        $validated = $request->validate([
            'alamat_pengiriman' => 'required|string|max:500',
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id_varian' => 'required|exists:variants,id_varian',
            'cartItems.*.kuantitas' => 'required|integer|min:1',
        ]);

        $cartItemsInput = $validated['cartItems'];
        $variantIds = collect($cartItemsInput)->pluck('id_varian')->toArray();
        $variants = Variant::with('product.toko')->whereIn('id_varian', $variantIds)->get()->keyBy('id_varian');

        $itemsPerToko = [];
        $createdOrders = collect(); // <-- Inisialisasi collection untuk menampung pesanan yang dibuat

        // 2. Cek Stok Final & Kelompokkan per Toko
        foreach ($cartItemsInput as $item) {
            $variant = $variants->get($item['id_varian']);
            $kuantitasDipesan = $item['kuantitas'];

            // Cek stok
            if ($variant->stok < $kuantitasDipesan) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi untuk ' . $variant->nama_varian,
                    'variant_id' => $variant->id_varian,
                ], 400);
            }

            // Tambahkan pengecekan null safety
            if (!$variant->product || !$variant->product->toko) {
                return response()->json(['message' => 'Gagal mengidentifikasi toko untuk varian: ' . $variant->nama_varian], 400);
            }

            $tokoId = $variant->product->toko->id;
            $itemsPerToko[$tokoId][] = [
                'variant' => $variant,
                'kuantitas' => $kuantitasDipesan,
            ];
        }

        // 3. DB Transaction
        try {
            // Tambahkan &$createdOrders ke use() agar bisa diubah di dalam closure
            DB::transaction(function () use ($itemsPerToko, $user, $validated, &$createdOrders) {
                foreach ($itemsPerToko as $tokoId => $items) {
                    $totalHargaPesanan = 0;

                    // Hitung total
                    foreach ($items as $item) {
                        $totalHargaPesanan += $item['variant']->harga * $item['kuantitas'];
                    }

                    // A. Buat Pesanan
                    $pesanan = Pesanan::create([
                        'user_id' => $user->id,
                        'toko_id' => $tokoId,
                        'total_harga' => $totalHargaPesanan,
                        'status' => 'pending',
                        'alamat_pengiriman' => $validated['alamat_pengiriman'],
                    ]);

                    // KUNCI PERBAIKAN: Simpan pesanan yang baru dibuat
                    $createdOrders->push($pesanan);

                    // B. Buat Detail Pesanan & Kurangi Stok
                    foreach ($items as $item) {
                        $variant = $item['variant'];
                        $kuantitas = $item['kuantitas'];

                        $pesanan->detailPesanans()->create([
                            'id_varian' => $variant->id_varian,
                            'kuantitas' => $kuantitas,
                            'harga' => $variant->harga,
                        ]);

                        $variant->decrement('stok', $kuantitas);
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses pesanan: ' . $e->getMessage()], 500);
        }

        // Ambil ID pesanan yang baru dibuat
        $orderIds = $createdOrders->pluck('id');

        // 4. Sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan Anda berhasil dibuat!',
            'order_ids' => $orderIds, // <-- Mengirim ID ke Frontend
        ], 201);
    }

    public function show($id)
    {
        $pesanan = Pesanan::with([
            'user',
            'toko',
            'detailPesanans.variant',
        ])->find($id);

        if (!$pesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pesanan,
        ], 200);
    }

}
