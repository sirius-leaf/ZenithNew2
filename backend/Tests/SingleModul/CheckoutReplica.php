<?php

namespace Tests\SingleModul;

use Illuminate\Support\Facades\DB;
use App\Models\Variant;
use App\Models\Pesanan;

class CheckoutReplica
{
    public function process(array $cart, int $userId, string $alamatPengiriman): array
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
            ->get();

        $itemsPerToko = [];

        foreach ($variants as $variant) {
            $kuantitas = $cart[$variant->id_varian]['kuantitas'] ?? 0;
            if ($kuantitas <= 0) continue;

            if ($variant->stok < $kuantitas) {
                return [
                    'success' => false,
                    'message' => 'Stok untuk ' . $variant->nama_varian . ' tidak mencukupi.',
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
            $pesananIds = DB::transaction(function () use ($itemsPerToko, $userId, $alamatPengiriman) {
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
                        'alamat_pengiriman' => $alamatPengiriman
                    ]);

                    foreach ($items as $item) {
                        $pesanan->detailPesanans()->create([
                            'id_varian' => $item['variant']->id_varian,
                            'kuantitas' => $item['kuantitas'],
                            'harga' => $item['variant']->harga
                        ]);
                        $item['variant']->decrement('stok', $item['kuantitas']);
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
                'message' => 'Gagal memproses pesanan.',
                'pesanan_ids' => []
            ];
        }
    }
}
