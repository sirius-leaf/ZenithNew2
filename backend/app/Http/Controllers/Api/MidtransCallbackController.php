<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Konfigurasi Midtrans (Wajib agar bisa membaca notifikasi)
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED', true);
        Config::$is3ds = env('MIDTRANS_IS_3DS', true);

        try {
            // 2. Ambil Notifikasi resmi dari Midtrans
            $notif = new Notification();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid notification'], 400);
        }

        // 3. Ekstrak Data Penting
        $transactionStatus = $notif->transaction_status;
        $type = $notif->payment_type;
        $orderIdMidtrans = $notif->order_id; // Contoh format: TRX-173200555-7
        $fraud = $notif->fraud_status;

        // 4. Cari Pesanan di Database
        // Kita perlu memecah string "TRX-TIMESTAMP-USERID" untuk dapat User ID
        // Karena sistem checkout Anda menggabungkan banyak pesanan dalam satu pembayaran,
        // kita akan mencari semua pesanan 'pending' milik user tersebut.

        $parts = explode('-', $orderIdMidtrans);
        $userIdFromTrx = end($parts); // Ambil angka terakhir (User ID)

        // Cari pesanan milik user ini yang masih pending
        $orders = Pesanan::where('user_id', $userIdFromTrx)
                         ->where('status', 'pending')
                         ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Order not found or already paid'], 404);
        }

        // 5. Tentukan Status Baru Berdasarkan Respon Midtrans
        $newStatus = null;

        if ($transactionStatus == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $newStatus = 'pending';
                } else {
                    $newStatus = 'paid';
                }
            }
        } else if ($transactionStatus == 'settlement') {
            // Ini status SUKSES untuk transfer bank, gopay, dll
            $newStatus = 'paid';
        } else if ($transactionStatus == 'pending') {
            $newStatus = 'pending';
        } else if ($transactionStatus == 'deny') {
            $newStatus = 'failed';
        } else if ($transactionStatus == 'expire') {
            $newStatus = 'expired';
        } else if ($transactionStatus == 'cancel') {
            $newStatus = 'canceled';
        }

        // 6. Update Semua Pesanan Terkait
        if ($newStatus) {
            foreach ($orders as $order) {
                $order->update([
                    'status' => $newStatus,
                    'paid_at' => ($newStatus === 'paid') ? now() : null
                ]);
            }
        }

        return response()->json(['message' => 'Callback received successfully']);
    }
}
