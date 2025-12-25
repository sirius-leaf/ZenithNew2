<?php

namespace Tests\SingleModul;

use Illuminate\Support\Facades\DB;
use App\Models\Variant;
use App\Models\Pesanan;
use App\Models\User;

class OrderFlowReplica
{
    /**
     * Proses checkout: buat pesanan dari keranjang
     */
    public function checkout(array $cart, int $userId, string $alamatPengiriman, string $paymentMethod = 'midtrans'): array
    {
        if (empty($cart)) {
            return [
                'success' => false,
                'message' => 'Keranjang Anda kosong.',
                'pesanan_ids' => []
            ];
        }

        $variantIds = array_keys($cart);
        $variants = Variant::with('product.toko')
            ->whereIn('id_varian', $variantIds)
            ->get()
            ->keyBy('id_varian');

        $itemsPerToko = [];

        foreach ($cart as $variantId => $itemData) {
            $kuantitas = $itemData['kuantitas'] ?? 0;
            if ($kuantitas <= 0) continue;

            if (!isset($variants[$variantId])) {
                return [
                    'success' => false,
                    'message' => 'Varian tidak ditemukan.',
                    'pesanan_ids' => []
                ];
            }

            $variant = $variants[$variantId];
            if ($variant->stok < $kuantitas) {
                return [
                    'success' => false,
                    'message' => 'Stok untuk ' . ($variant->nama_varian ?? 'produk ini') . ' tidak mencukupi.',
                    'pesanan_ids' => []
                ];
            }

            if (!$variant->product || !$variant->product->toko) {
                return [
                    'success' => false,
                    'message' => 'Produk tidak terkait dengan toko yang valid.',
                    'pesanan_ids' => []
                ];
            }

            $tokoId = $variant->product->toko->id;
            $itemsPerToko[$tokoId][] = [
                'variant' => $variant,
                'kuantitas' => $kuantitas
            ];
        }

        try {
            $pesananIds = DB::transaction(function () use ($itemsPerToko, $userId, $alamatPengiriman, $paymentMethod) {
                $ids = [];
                foreach ($itemsPerToko as $tokoId => $items) {
                    $total = 0;
                    foreach ($items as $item) {
                        $total += $item['variant']->harga * $item['kuantitas'];
                    }

                    $pesanan = Pesanan::create([
                        'user_id' => $userId,
                        'toko_id' => $tokoId,
                        'total_harga' => $total,
                        'status' => 'pending',
                        'alamat_pengiriman' => $alamatPengiriman,
                        'payment_method' => $paymentMethod
                    ]);

                    foreach ($items as $item) {
                        $pesanan->detailPesanans()->create([
                            'id_varian' => $item['variant']->id_varian,
                            'kuantitas' => $item['kuantitas'],
                            'harga' => $item['variant']->harga
                        ]);
                        // Gunakan query builder agar aman dalam transaksi
                        Variant::where('id_varian', $item['variant']->id_varian)
                            ->decrement('stok', $item['kuantitas']);
                    }

                    $ids[] = $pesanan->id;
                }
                return $ids;
            });

            return [
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'pesanan_ids' => $pesananIds
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memproses checkout: ' . $e->getMessage(),
                'pesanan_ids' => []
            ];
        }
    }

    /**
     * Update status pesanan (untuk seller atau buyer)
     */
    public function updateStatus(int $orderId, int $userId, string $status, ?string $resi = null): array
    {
        $pesanan = Pesanan::with('detailPesanans')->findOrFail($orderId);
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan.'];
        }

        $isSeller = $user->toko && $user->toko->id == $pesanan->toko_id;
        $isBuyj = $user->id == $pesanan->user_id;

        if (!$isSeller && !$isBuyj) {
            return ['success' => false, 'message' => 'Unauthorized. Anda bukan pembeli atau seller.'];
        }

        $allowedStatuses = ['confirmed', 'packed', 'shipped', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Status tidak valid.'];
        }

        if ($status === 'completed') {
            if ($isBuyj && !in_array($pesanan->status, ['shipped', 'packed'])) {
                return ['success' => false, 'message' => 'Pesanan belum dikirim; tidak dapat dikonfirmasi sebagai diterima.'];
            }
        } else {
            if (!$isSeller) {
                return ['success' => false, 'message' => 'Unauthorized. Hanya seller yang bisa mengubah status ini.'];
            }
        }

        $pesanan->status = $status;
        if ($resi !== null) {
            $pesanan->resi = $resi;
        }
        $pesanan->save();

        return ['success' => true, 'message' => 'Status diperbarui.', 'data' => $pesanan];
    }

    /**
     * Cancel pesanan oleh buyer
     */
    public function cancel(int $orderId, int $userId, string $alasan): array
    {
        $pesanan = Pesanan::where('id', $orderId)->where('user_id', $userId)->first();
        if (!$pesanan) {
            return ['success' => false, 'message' => 'Pesanan tidak ditemukan atau akses ditolak.'];
        }

        $allowedStatuses = ['pending', 'paid', 'confirmed'];
        if (!in_array($pesanan->status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Pesanan tidak dapat dibatalkan pada status: ' . $pesanan->status];
        }

        $pesanan->previous_status = $pesanan->status;
        $pesanan->status = 'cancellation_requested';
        $pesanan->alasan_pembatalan = $alasan;
        $pesanan->save();

        return ['success' => true, 'message' => 'Pengajuan pembatalan dikirim.', 'data' => $pesanan];
    }

    /**
     * Approve pembatalan oleh seller
     */
    public function approveCancellation(int $orderId, int $sellerId): array
    {
        $pesanan = Pesanan::with('detailPesanans')->findOrFail($orderId);

        if (!$this->isUserSellerOfOrder($sellerId, $pesanan->toko_id)) {
            return ['success' => false, 'message' => 'Unauthorized. Bukan seller toko ini.'];
        }

        if ($pesanan->status !== 'cancellation_requested') {
            return ['success' => false, 'message' => 'Status tidak valid untuk disetujui.'];
        }

        $pesanan->status = 'cancelled';
        $pesanan->save();

        foreach ($pesanan->detailPesanans as $detail) {
            Variant::where('id_varian', $detail->id_varian)
                ->increment('stok', $detail->kuantitas);
        }

        return ['success' => true, 'message' => 'Pembatalan disetujui.', 'data' => $pesanan];
    }

    /**
     * Reject pembatalan oleh seller
     */
    public function rejectCancellation(int $orderId, int $sellerId): array
    {
        $pesanan = Pesanan::findOrFail($orderId);

        if (!$this->isUserSellerOfOrder($sellerId, $pesanan->toko_id)) {
            return ['success' => false, 'message' => 'Unauthorized.'];
        }

        if ($pesanan->status !== 'cancellation_requested') {
            return ['success' => false, 'message' => 'Status tidak valid untuk ditolak.'];
        }

        $newStatus = $pesanan->previous_status ?? 'confirmed';
        if ($newStatus === 'paid') {
            $newStatus = 'confirmed';
        }

        $pesanan->status = $newStatus;
        $pesanan->is_cancellation_rejected = true;
        $pesanan->save();

        return ['success' => true, 'message' => 'Pembatalan ditolak.', 'data' => $pesanan];
    }

    protected function isUserSellerOfOrder(int $userId, int $tokoId): bool
    {
        $user = User::find($userId);
        return $user && $user->toko && $user->toko->id == $tokoId;
    }
}
