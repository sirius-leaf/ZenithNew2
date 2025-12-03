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
    /**
     * @OA\Post(
     *     path="/api/order/preview",
     *     tags={"Orders"},
     *     summary="Preview order (check stock and calculate total)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cartItems"},
     *             @OA\Property(
     *                 property="cartItems",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id_varian", type="integer"),
     *                     @OA\Property(property="kuantitas", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order preview",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=400, description="Stock insufficient or self-purchase")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/order/history",
     *     tags={"Orders"},
     *     summary="Get user order history",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
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
     * Get orders for the seller's store.
     *
     * @OA\Get(
     *     path="/api/manage/orders",
     *     tags={"Orders"},
     *     summary="Get seller orders",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of seller orders",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function sellerIndex()
    {
        $user = Auth::user();
        $toko = $user->toko;

        if (!$toko) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki toko.'
            ], 404);
        }

        $orders = Pesanan::where('toko_id', $toko->id)
            ->with(['user', 'detailPesanans.variant.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Memproses pesanan dan Generate Midtrans Token.
     *
     * @OA\Post(
     *     path="/api/order/store",
     *     tags={"Orders"},
     *     summary="Create new order",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"alamat_pengiriman","payment_method","cartItems"},
     *             @OA\Property(property="alamat_pengiriman", type="string"),
     *             @OA\Property(property="payment_method", type="string", enum={"cod","transfer"}),
     *             @OA\Property(
     *                 property="cartItems",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id_varian", type="integer"),
     *                     @OA\Property(property="kuantitas", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=400, description="Stock insufficient or self-purchase")
     * )
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
                        // Jika COD, langsung 'confirmed' agar seller bisa proses. Jika Transfer, 'pending' tunggu bayar.
                        'status' => $validated['payment_method'] === 'cod' ? 'confirmed' : 'pending',
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

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get order details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show($id)
    {
        $user = Auth::user();

        // Cari pesanan berdasarkan ID dan pastikan milik user yang login
        $pesanan = Pesanan::with(['detailPesanans.variant.product', 'toko.user', 'reviews.images'])
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
     *
     * @OA\Patch(
     *     path="/api/order/{id}/status",
     *     tags={"Orders"},
     *     summary="Update order status",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"confirmed","packed","shipped","completed","cancelled"}),
     *             @OA\Property(property="resi", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
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

        // Authorization
        // - Seller dapat merubah status kecuali 'completed'
        // - User dapat merubah status menjadi 'completed' jika sudah diterima
        $requestedStatus = $request->status;

        if ($requestedStatus === 'completed') {
            if ($pesanan->user_id === $user->id && !in_array($pesanan->status, ['shipped', 'packed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan belum dikirim; tidak dapat dikonfirmasi sebagai diterima.'
                ], 400);
            }

            if ($pesanan->user_id !== $user->id && (!$user->toko || $user->toko->id !== $pesanan->toko_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Anda bukan pemilik toko atau pembeli pesanan ini.'
                ], 403);
            }
        } else {
            if (!$user->toko || $user->toko->id !== $pesanan->toko_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Anda bukan pemilik toko pesanan ini.'
                ], 403);
            }
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

    /**
     * Cancel order by User
     *
     * @OA\Post(
     *     path="/api/orders/{id}/cancel",
     *     tags={"Orders"},
     *     summary="Cancel order",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"alasan"},
     *             @OA\Property(property="alasan", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cancellation requested",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=400, description="Cannot cancel")
     * )
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        $pesanan = Pesanan::where('id', $id)->where('user_id', $user->id)->first();

        if (!$pesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan atau akses ditolak.'
            ], 404);
        }

        // Cek apakah status masih bisa dibatalkan
        $allowedStatuses = ['pending', 'paid', 'confirmed'];

        if (!in_array($pesanan->status, $allowedStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak dapat dibatalkan karena status saat ini: ' . $pesanan->status
            ], 400);
        }

        $request->validate([
            'alasan' => 'required|string|max:500'
        ]);

        // Simpan status sebelumnya dan ubah ke 'cancellation_requested'
        $pesanan->previous_status = $pesanan->status;
        $pesanan->status = 'cancellation_requested';
        $pesanan->alasan_pembatalan = $request->alasan;
        $pesanan->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan pembatalan berhasil dikirim. Menunggu persetujuan seller.',
            'data' => $pesanan
        ]);
    }

    /**
     * Approve Cancellation (Seller)
     *
     * @OA\Post(
     *     path="/api/orders/{id}/approve-cancellation",
     *     tags={"Orders"},
     *     summary="Approve order cancellation (Seller)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cancellation approved",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function approveCancellation($id)
    {
        $user = Auth::user();
        $pesanan = Pesanan::findOrFail($id);

        // Pastikan user adalah pemilik toko
        if (!$user->toko || $user->toko->id !== $pesanan->toko_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($pesanan->status !== 'cancellation_requested') {
            return response()->json(['message' => 'Status pesanan tidak valid untuk disetujui.'], 400);
        }

        $pesanan->status = 'cancelled';
        $pesanan->save();

        // Kembalikan stok
        foreach ($pesanan->detailPesanans as $detail) {
            $variant = Variant::find($detail->id_varian);
            if ($variant) {
                $variant->increment('stok', $detail->kuantitas);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pembatalan disetujui.',
            'data' => $pesanan
        ]);
    }

    /**
     * Reject Cancellation (Seller)
     *
     * @OA\Post(
     *     path="/api/orders/{id}/reject-cancellation",
     *     tags={"Orders"},
     *     summary="Reject order cancellation (Seller)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cancellation rejected",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function rejectCancellation($id)
    {
        $user = Auth::user();
        $pesanan = Pesanan::findOrFail($id);

        // Pastikan user adalah pemilik toko
        if (!$user->toko || $user->toko->id !== $pesanan->toko_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($pesanan->status !== 'cancellation_requested') {
            return response()->json(['message' => 'Status pesanan tidak valid untuk ditolak.'], 400);
        }

        // Kembalikan ke status sebelumnya atau default ke 'confirmed'
        // Jika status sebelumnya 'paid', kita ubah ke 'confirmed' agar seller bisa langsung proses (kemas)
        // Jika 'pending', tetap 'pending'
        $newStatus = $pesanan->previous_status ?? 'confirmed';

        if ($newStatus === 'paid') {
            $newStatus = 'confirmed';
        }

        $pesanan->status = $newStatus;
        $pesanan->is_cancellation_rejected = true;
        $pesanan->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pembatalan ditolak. Status dikembalikan.',
            'data' => $pesanan
        ]);
    }

    /**
     * Mark order as paid (Called by Frontend after Midtrans success)
     * Workaround for localhost webhook issue.
     *
     * @OA\Post(
     *     path="/api/orders/{id}/pay",
     *     tags={"Orders"},
     *     summary="Mark order as paid (Manual/Frontend)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order marked as paid",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function markAsPaid(Request $request, $id)
    {
        $user = Auth::user();
        $pesanan = Pesanan::where('id', $id)->where('user_id', $user->id)->first();

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($pesanan->status === 'pending') {
            $pesanan->status = 'paid';
            $pesanan->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status pesanan diperbarui menjadi dibayar.',
            'data' => $pesanan
        ]);
    }
}
