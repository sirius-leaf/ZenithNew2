<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// ✅ 1. Import Library Midtrans
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function preview(Request $request)
    {
        // ... (Bagian preview tidak berubah, biarkan sama seperti sebelumnya) ...
        $request->validate([
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id_varian' => 'required|exists:variants,id_varian',
            'cartItems.*.kuantitas' => 'required|integer|min:1',
        ]);

        $cartItemsInput = $request->input('cartItems');
        $variantIds = collect($cartItemsInput)->pluck('id_varian')->toArray();

        $variants = Variant::with('product.toko')
            ->whereIn('id_varian', $variantIds)
            ->get()
            ->keyBy('id_varian');

        $cartSummary = [];
        $totalPrice = 0;

        foreach ($cartItemsInput as $item) {
            $variant = $variants->get($item['id_varian']);
            $kuantitas = $item['kuantitas'];

            if (!$variant)
                continue;

            // 🛑 CEK SELF-PURCHASE
            // Jika user punya toko, dan toko user sama dengan toko produk ini -> ERROR
            $userToko = Auth::user()->toko;
            if ($userToko && $variant->product && $variant->product->toko && $userToko->id === $variant->product->toko->id) {
                return response()->json([
                    'message' => 'Anda tidak dapat membeli produk dari toko Anda sendiri (' . $variant->product->nama_produk . ')',
                    'variant_id' => $variant->id_varian
                ], 400);
            }

            if ($variant->stok < $kuantitas) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi untuk ' . $variant->product->nama_produk . ' (' . $variant->nama_varian . ')',
                    'variant_id' => $variant->id_varian
                ], 400);
            }

            $subtotal = $variant->harga * $kuantitas;
            $totalPrice += $subtotal;

            $cartSummary[] = [
                'variant' => $variant,
                'kuantitas' => $kuantitas,
                'subtotal' => $subtotal
            ];
        }

        return response()->json([
            'status' => 'success',
            'cartItems' => $cartSummary,
            'totalPrice' => $totalPrice
        ], 200);
    }

    public function index()
    {
        $orders = Auth::user()->pesanans()->with(['toko', 'detailPesanans.variant.product'])->get();
        //

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Memproses pesanan dan Generate Midtrans Token.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi
        $validated = $request->validate([
            'alamat_pengiriman' => 'required|string|max:500',
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id_varian' => 'required|exists:variants,id_varian',
            'cartItems.*.kuantitas' => 'required|integer|min:1',
            // Pastikan frontend mengirim 'payment_method'
            'payment_method' => 'required|string',
        ]);

        $cartItemsInput = $validated['cartItems'];
        $variantIds = collect($cartItemsInput)->pluck('id_varian')->toArray();
        $variants = Variant::with('product.toko')->whereIn('id_varian', $variantIds)->get()->keyBy('id_varian');

        $itemsPerToko = [];
        $createdOrders = collect();

        // 2. Cek Stok & Kelompokkan
        foreach ($cartItemsInput as $item) {
            $variant = $variants->get($item['id_varian']);
            $kuantitasDipesan = $item['kuantitas'];

            if ($variant->stok < $kuantitasDipesan) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi untuk ' . $variant->nama_varian,
                    'variant_id' => $variant->id_varian
                ], 400);
            }

            // 🛑 CEK SELF-PURCHASE (Double check di backend saat checkout final)
            $userToko = Auth::user()->toko;
            if ($userToko && $variant->product && $variant->product->toko && $userToko->id === $variant->product->toko->id) {
                return response()->json([
                    'message' => 'Anda tidak dapat membeli produk dari toko Anda sendiri (' . $variant->product->nama_produk . ')',
                    'variant_id' => $variant->id_varian
                ], 400);
            }

            if (!$variant->product || !$variant->product->toko) {
                return response()->json(['message' => 'Gagal mengidentifikasi toko.'], 400);
            }

            $tokoId = $variant->product->toko->id;
            $itemsPerToko[$tokoId][] = [
                'variant' => $variant,
                'kuantitas' => $kuantitasDipesan
            ];
        }

        // 3. DB Transaction (Buat Pesanan di Database)
        try {
            DB::transaction(function () use ($itemsPerToko, $user, $validated, &$createdOrders) {
                foreach ($itemsPerToko as $tokoId => $items) {
                    $totalHargaPesanan = 0;

                    foreach ($items as $item) {
                        $totalHargaPesanan += $item['variant']->harga * $item['kuantitas'];
                    }

                    $pesanan = Pesanan::create([
                        'user_id' => $user->id,
                        'toko_id' => $tokoId,
                        'total_harga' => $totalHargaPesanan,
                        'status' => 'pending',
                        'alamat_pengiriman' => $validated['alamat_pengiriman'],
                        'payment_method' => $validated['payment_method'] // Simpan metode pembayaran
                    ]);

                    $createdOrders->push($pesanan);

                    foreach ($items as $item) {
                        $variant = $item['variant'];
                        $kuantitas = $item['kuantitas'];

                        $pesanan->detailPesanans()->create([
                            'id_varian' => $variant->id_varian,
                            'kuantitas' => $kuantitas,
                            'harga' => $variant->harga
                        ]);

                        $variant->decrement('stok', $kuantitas);
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses pesanan: ' . $e->getMessage()], 500);
        }

        $orderIds = $createdOrders->pluck('id');

        // ============================================================
        // ✅ 4. INTEGRASI MIDTRANS (Hanya jika bukan COD)
        // ============================================================
        $snapToken = null;

        // Kita hanya generate token jika metode pembayaran adalah transfer/midtrans
        if ($validated['payment_method'] !== 'cod') {

            // A. Konfigurasi Midtrans
            Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            Config::$isSanitized = env('MIDTRANS_IS_SANITIZED', true);
            Config::$is3ds = env('MIDTRANS_IS_3DS', true);

            // B. Hitung Total Pembayaran (Gabungan semua pesanan toko)
            $totalGrossAmount = 0;
            foreach ($createdOrders as $order) {
                $totalGrossAmount += $order->total_harga;
            }

            // C. Buat ID Transaksi Unik Gabungan
            // Format: TRX-{TIMESTAMP}-{USER_ID}
            $transactionId = 'TRX-' . time() . '-' . $user->id;

            // D. Siapkan Parameter Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $transactionId,
                    'gross_amount' => (int) $totalGrossAmount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    // 'phone' => $user->no_telpon, // Tambahkan jika ada kolom no_telpon
                ],
                // Opsional: Item details bisa ditambahkan jika ingin detail di email Midtrans
            ];

            try {
                // E. Minta Snap Token ke Midtrans
                $snapToken = Snap::getSnapToken($params);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal menghubungi gateway pembayaran: ' . $e->getMessage()], 500);
            }
        }

        // 5. Sukses Return JSON
        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan Anda berhasil dibuat!',
            'order_ids' => $orderIds,
            'snap_token' => $snapToken, // <-- Kirim token ke Vue
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();

        // Cari pesanan berdasarkan ID dan pastikan milik user yang login
        $pesanan = Pesanan::with(['detailPesanans.variant.product', 'toko'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$pesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan atau akses ditolak.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pesanan
        ], 200);
    }

    /**
     * Update status pesanan (Seller Only)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'status' => 'required|string|in:confirmed,packed,shipped,completed,cancelled',
            'resi' => 'nullable|string|max:255'
        ]);

        // Cari pesanan
        $pesanan = Pesanan::findOrFail($id);

        // Pastikan user adalah pemilik toko dari pesanan ini
        // Kita asumsikan user->toko->id harus sama dengan pesanan->toko_id
        if (!$user->toko || $user->toko->id !== $pesanan->toko_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Anda bukan pemilik toko pesanan ini.'
            ], 403);
        }

        // Update status
        $pesanan->status = $request->status;

        // Update resi jika ada (biasanya saat status 'shipped')
        if ($request->has('resi') && $request->resi) {
            $pesanan->resi = $request->resi;
        }

        $pesanan->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => $pesanan
        ]);
    }
}
