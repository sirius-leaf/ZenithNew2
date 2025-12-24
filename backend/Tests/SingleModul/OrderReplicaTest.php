<?php

namespace Tests\SingleModul;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Toko;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Tests\SingleModul\OrderReplica;

class OrderReplicaTest extends TestCase
{
    use RefreshDatabase;

    protected function createOrder(int $buyerId, int $tokoId, string $status = 'pending'): Pesanan
    {
        $pesanan = Pesanan::create([
            'user_id' => $buyerId,
            'toko_id' => $tokoId,
            'total_harga' => 100000,
            'status' => $status,
            'alamat_pengiriman' => 'Jl. Test',
            'payment_method' => 'transfer',
        ]);

        // Tambahkan detail pesanan (butuh untuk kembalikan stok saat cancel)
        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'id_varian' => $this->variant->id_varian,
            'kuantitas' => 2,
            'harga' => 50000
        ]);

        return $pesanan;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user buyer & seller
        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();

        // Buat toko milik seller
        $this->toko = Toko::factory()->create(['id_user' => $this->seller->id]);

        // Produk & varian
        $product = Product::factory()->create(['id_toko' => $this->toko->id]);
        $this->variant = Variant::factory()->create([
            'id_produk' => $product->id_produk,
            'stok' => 10,
            'harga' => 50000
        ]);
    }

    /** @test */
    public function seller_can_update_order_status_to_packed()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'confirmed');

        $replica = new OrderReplica();
        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $this->seller->id,
            status: 'packed'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('packed', $pesanan->fresh()->status);
    }

    /** @test */
    public function buyer_can_mark_order_as_completed()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'shipped');

        $replica = new OrderReplica();
        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            status: 'completed'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('completed', $pesanan->fresh()->status);
    }

    /** @test */
    public function buyer_cannot_complete_order_if_not_shipped()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'confirmed');

        $replica = new OrderReplica();
        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            status: 'completed'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('belum dikirim', $result['message']);
    }

    /** @test */
    public function non_seller_cannot_update_order_status()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'confirmed');

        // User lain (bukan seller, bukan buyer)
        $otherUser = User::factory()->create();

        $replica = new OrderReplica();
        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $otherUser->id,
            status: 'packed'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Unauthorized', $result['message']);
    }

    /** @test */
    public function user_can_cancel_order_in_allowed_status()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'confirmed');

        $replica = new OrderReplica();
        $result = $replica->cancel(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            alasan: 'Tidak jadi beli'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('cancellation_requested', $pesanan->fresh()->status);
        $this->assertEquals('Tidak jadi beli', $pesanan->fresh()->alasan_pembatalan);
    }

    /** @test */
    public function user_cannot_cancel_order_if_already_shipped()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'shipped');

        $replica = new OrderReplica();
        $result = $replica->cancel(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            alasan: 'Terlambat'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak dapat dibatalkan', $result['message']);
    }

    /** @test */
    public function seller_can_approve_cancellation_and_restore_stock()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'confirmed');
        $originalStok = $this->variant->stok;

        // User cancel
        $this->variant->decrement('stok', 2);

        $replica = new OrderReplica();
        $replica->cancel($pesanan->id, $this->buyer->id, 'Batalkan');

        // Seller approve
        $result = $replica->approveCancellation($pesanan->id, $this->seller->id);

        $this->assertTrue($result['success']);
        $this->assertEquals('cancelled', $pesanan->fresh()->status);

        // Cek stok kembali
        $this->assertEquals($originalStok, $this->variant->fresh()->stok);
    }

    /** @test */
    public function seller_can_reject_cancellation_and_restore_previous_status()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'paid');

        $replica = new OrderReplica();
        $replica->cancel($pesanan->id, $this->buyer->id, 'Salah pilih');

        // Seller reject
        $result = $replica->rejectCancellation($pesanan->id, $this->seller->id);

        $this->assertTrue($result['success']);
        // 'paid' harus jadi 'confirmed' setelah reject
        $this->assertEquals('confirmed', $pesanan->fresh()->status);
        $this->assertTrue($pesanan->fresh()->is_cancellation_rejected);
    }

    /** @test */
    public function buyer_can_mark_order_as_paid()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'pending');

        $replica = new OrderReplica();
        $result = $replica->markAsPaid($pesanan->id, $this->buyer->id);

        $this->assertTrue($result['success']);
        $this->assertEquals('paid', $pesanan->fresh()->status);
    }

    /** @test */
    public function buyer_cannot_mark_non_pending_order_as_paid()
    {
        $pesanan = $this->createOrder($this->buyer->id, $this->toko->id, 'confirmed');

        $replica = new OrderReplica();
        $replica->markAsPaid($pesanan->id, $this->buyer->id);

        // Tetap 'confirmed', tidak berubah
        $this->assertEquals('confirmed', $pesanan->fresh()->status);
    }
}
