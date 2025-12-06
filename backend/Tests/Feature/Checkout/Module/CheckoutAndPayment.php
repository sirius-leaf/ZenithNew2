<?php

namespace Tests\Unit\Checkout\Module;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Variant;
use App\Models\Product;

class CheckoutAndPayment
{
    /**
     * 1. User membuka keranjang dan melanjutkan ke checkout.
     * Menggunakan data cart dari session (seperti AddToCart.php).
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil data keranjang dari session
        // Data cart format: [id_varian => ['kuantitas' => x], ...]
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['message' => 'Keranjang kosong.'], 400);
        }

        // 2. Validate and Group Items by Toko (Assume items might come from different shops)
        $groupedItems = [];
        $totalHargaPerToko = [];

        foreach ($cart as $id_varian => $itemData) {
            $variant = Variant::with('product')->find($id_varian);
            if (!$variant) continue;

            // Cek Stok
            if ($variant->stok < $itemData['kuantitas']) {
                return response()->json(['message' => "Stok untuk {$variant->nama_varian} tidak mencukupi."], 400);
            }

            $tokoId = $variant->product->id_toko;
            
            // Hitung harga
            $subtotal = $variant->harga * $itemData['kuantitas'];

            $groupedItems[$tokoId][] = [
                'variant' => $variant,
                'kuantitas' => $itemData['kuantitas'],
                'harga_satuan' => $variant->harga,
                'subtotal' => $subtotal
            ];

            if (!isset($totalHargaPerToko[$tokoId])) {
                $totalHargaPerToko[$tokoId] = 0;
            }
            $totalHargaPerToko[$tokoId] += $subtotal;
        }

        if (empty($groupedItems)) {
            return response()->json(['message' => 'Tidak ada item valid di keranjang.'], 400);
        }

        DB::beginTransaction();
        try {
            $createdOrders = [];

            // 3. Buat Record Pesanan (Order) per Toko
            foreach ($groupedItems as $tokoId => $items) {
                // Buat Pesanan
                $pesanan = Pesanan::create([
                    'user_id' => $user->id,
                    'toko_id' => $tokoId,
                    'total_harga' => $totalHargaPerToko[$tokoId],
                    'status' => 'pending', // Menunggu pembayaran
                    'payment_method' => $request->payment_method ?? 'transfer', // Default transfer
                    'alamat_pengiriman' => $request->address ?? $user->address ?? 'Alamat Default',
                ]);

                // 4. Isi Tabel DetailPesanan (Items)
                foreach ($items as $item) {
                    DetailPesanan::create([
                        'pesanan_id' => $pesanan->id,
                        'id_varian' => $item['variant']->id_varian,
                        'kuantitas' => $item['kuantitas'],
                        'harga' => $item['harga_satuan']
                    ]);

                    // Kurangi Stok Varian
                    $item['variant']->decrement('stok', $item['kuantitas']);
                }

                $createdOrders[] = $pesanan;
            }

            // Hapus Keranjang setelah Checkout berhasil
            session()->forget('cart');

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Checkout berhasil. Silakan lakukan pembayaran.',
                'orders' => $createdOrders
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 2. User menekan tombol Bayar dan menyelesaikan pembayaran.
     * 4. Sistem memperbarui status pesanan.
     */
    public function processPayment(Request $request, $orderId)
    {
        // 4. Sistem memperbarui status pesanan dan pembayaran sesuai hasil transaksi
        
        $pesanan = Pesanan::find($orderId);

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($pesanan->status !== 'pending') {
            return response()->json(['message' => 'Pesanan sudah dibayar atau dibatalkan.'], 400);
        }

        // Simulasikan Notifikasi Pembayaran Sukses
        // (Misal dari Midtrans atau Payment Gateway)
        $paymentStatus = 'success'; // Hardcode sukses untuk simulasi

        if ($paymentStatus === 'success') {
            DB::beginTransaction();
            try {
                // Update Status Pesanan: Menunggu Pembayaran -> Dibayar
                $pesanan->update([
                    'status' => 'paid',
                ]);

                // 3. Status pada tabel payments berubah sesuai notifikasi pembayaran
                // Note: Karena tabel 'payments' belum ada di migration, kita simulasi saja.
                // Jika tabel payments ada, kodenya kira-kira seperti ini:
                /*
                \App\Models\Payment::create([
                    'pesanan_id' => $pesanan->id,
                    'amount' => $pesanan->total_harga,
                    'status' => 'settlement', // atau 'paid'
                    'payment_type' => $pesanan->payment_method,
                    'transaction_time' => now()
                ]);
                */

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pembayaran berhasil dikonfirmasi. Status pesanan sekarang PAID.',
                    'data' => $pesanan
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Gagal memproses pembayaran.'], 500);
            }
        }

        return response()->json(['message' => 'Pembayaran gagal.'], 400);
    }
}
