<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Endpoint simulasi konfirmasi pembayaran.
     * Mengubah status pesanan dari 'pending' menjadi 'paid'.
     */
    public function simulate(Request $request, $order_id)
    {
        $order = Pesanan::where('id', $order_id)
                        ->where('user_id', Auth::id()) // Pastikan hanya user yang bersangkutan yang bisa konfirmasi
                        ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Pesanan sudah lunas atau dibatalkan.'], 400);
        }

        // 1. Simulasikan Pembayaran Sukses
        $order->update([
            'status' => 'paid',
            'paid_at' => now(), // Catat waktu pembayaran
            // Di sistem nyata, ini juga mencatat metode pembayaran dan ID transaksi
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran berhasil dikonfirmasi! Status pesanan kini PAID.'
        ], 200);
    }
}
