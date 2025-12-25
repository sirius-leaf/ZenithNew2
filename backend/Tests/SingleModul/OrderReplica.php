<?php

namespace Tests\SingleModul;

use App\Models\Pesanan;
use App\Models\Variant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderReplica
{
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
        $isBuyer = $user->id == $pesanan->user_id;

        if (!$isSeller && !$isBuyer) {
            return ['success' => false, 'message' => 'Unauthorized. Anda bukan pembeli atau seller.'];
        }

        $allowedStatuses = ['confirmed', 'packed', 'shipped', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Status tidak valid.'];
        }

        // Aturan khusus untuk 'completed'
        if ($status === 'completed') {
            if ($isBuyer && !in_array($pesanan->status, ['shipped', 'packed'])) {
                return ['success' => false, 'message' => 'Pesanan belum dikirim; tidak dapat dikonfirmasi sebagai diterima.'];
            }
            // ✅ Untuk COD: completed = pembayaran sukses (cash diterima)
            // Tidak perlu logika tambahan — cukup ubah status
        } else {
            // Hanya seller yang boleh ubah status selain 'completed'
            if (!$isSeller) {
                return ['success' => false, 'message' => 'Unauthorized. Hanya seller yang bisa mengubah status ini.'];
            }
        }

        // Update status
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
        $pesanan = Pesanan::findOrFail($orderId);

        if (!$this->isUserSellerOfOrder($sellerId, $pesanan->toko_id)) {
            return ['success' => false, 'message' => 'Unauthorized. Bukan seller toko ini.'];
        }

        if ($pesanan->status !== 'cancellation_requested') {
            return ['success' => false, 'message' => 'Status tidak valid untuk disetujui.'];
        }

        $pesanan->status = 'cancelled';
        $pesanan->save();

        // Kembalikan stok
        foreach ($pesanan->detailPesanans as $detail) {
            Variant::where('id_varian', $detail->id_varian)->increment('stok', $detail->kuantitas);
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
        if ($newStatus === 'paid') $newStatus = 'confirmed';

        $pesanan->status = $newStatus;
        $pesanan->is_cancellation_rejected = true;
        $pesanan->save();

        return ['success' => true, 'message' => 'Pembatalan ditolak.', 'data' => $pesanan];
    }

    // Helper
    protected function isUserSellerOfOrder(int $userId, int $tokoId): bool
    {
        $user = User::find($userId);
        return $user && $user->toko && $user->toko->id == $tokoId;
    }
}
