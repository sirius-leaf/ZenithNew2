<?php

namespace Tests\SingleModul;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Toko;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Pesanan;
use Tests\SingleModul\OrderFlowReplica;

class OrderFlowReplicaTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $seller;
    protected $toko;
    protected $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();
        $this->toko = Toko::factory()->create(['id_user' => $this->seller->id]);

        $product = Product::factory()->create(['id_toko' => $this->toko->id]);
        $this->variant = Variant::factory()->create([
            'id_produk' => $product->id_produk,
            'stok' => 10,
            'harga' => 50000,
            'nama_varian' => 'Varian Test'
        ]);
    }

    protected function createCart(int $kuantitas = 2): array
    {
        return [$this->variant->id_varian => ['kuantitas' => $kuantitas]];
    }

    /** @test */
    public function checkout_success_with_midtrans()
    {
        $cart = $this->createCart(2);
        $replica = new OrderFlowReplica();

        $result = $replica->checkout($cart, $this->buyer->id, 'Jl. Test', 'midtrans');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('pesanans', [
            'user_id' => $this->buyer->id,
            'toko_id' => $this->toko->id,
            'total_harga' => 100000,
            'status' => 'pending',
            'payment_method' => 'midtrans'
        ]);
        $this->assertDatabaseHas('variants', [
            'id_varian' => $this->variant->id_varian,
            'stok' => 8
        ]);
    }

    /** @test */
    public function checkout_success_with_cod()
    {
        $cart = $this->createCart(1);
        $replica = new OrderFlowReplica();

        $result = $replica->checkout($cart, $this->buyer->id, 'Jl. Test', 'cod');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('pesanans', [
            'payment_method' => 'cod'
        ]);
        $this->assertDatabaseHas('variants', [
            'stok' => 9
        ]);
    }

    /** @test */
    public function checkout_fails_if_stock_insufficient()
    {
        $cart = [$this->variant->id_varian => ['kuantitas' => 15]]; // stok hanya 10
        $replica = new OrderFlowReplica();

        $result = $replica->checkout($cart, $this->buyer->id, 'Jl. Test');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak mencukupi', $result['message']);
        $this->assertDatabaseMissing('pesanans', [
            'user_id' => $this->buyer->id
        ]);
    }

    /** @test */
    public function seller_can_update_order_status_to_packed()
    {
        $this->checkout_success_with_midtrans();
        $pesanan = Pesanan::first();

        $replica = new OrderFlowReplica();
        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $this->seller->id,
            status: 'packed'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('packed', $pesanan->fresh()->status);
    }

    /** @test */
    public function buyer_can_complete_order_for_cod()
    {
        $this->checkout_success_with_cod();
        $pesanan = Pesanan::first();

        // Seller kirim dulu
        $replica = new OrderFlowReplica();
        $replica->updateStatus($pesanan->id, $this->seller->id, 'shipped');

        // Buyer terima
        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            status: 'completed'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('completed', $pesanan->fresh()->status);
    }

    /** @test */
    public function buyer_cannot_complete_if_not_shipped()
    {
        $this->checkout_success_with_midtrans();
        $pesanan = Pesanan::first();

        $replica = new OrderFlowReplica();
        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            status: 'completed'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('belum dikirim', $result['message']);
    }

    /** @test */
    public function buyer_can_cancel_order()
    {
        $this->checkout_success_with_midtrans();
        $pesanan = Pesanan::first();

        $replica = new OrderFlowReplica();
        $result = $replica->cancel(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            alasan: 'Salah pilih'
        );

        $this->assertTrue($result['success']);
        $pesanan->refresh();
        $this->assertEquals('cancellation_requested', $pesanan->status);
        $this->assertEquals('Salah pilih', $pesanan->alasan_pembatalan);
    }

    /** @test */
    public function seller_can_approve_cancellation_and_restore_stock()
    {
        $originalStok = $this->variant->stok; // 10
        $this->checkout_success_with_midtrans();
        $pesanan = Pesanan::first();

        $replica = new OrderFlowReplica();
        $replica->cancel($pesanan->id, $this->buyer->id, 'Batalkan');
        $replica->approveCancellation($pesanan->id, $this->seller->id);

        $pesanan->refresh();
        $this->assertEquals('cancelled', $pesanan->status);
        $this->assertEquals($originalStok, $this->variant->fresh()->stok); // stok kembali ke 10
    }

    /** @test */
    public function seller_can_reject_cancellation()
    {
        $this->checkout_success_with_midtrans();
        $pesanan = Pesanan::first();

        // Ubah status ke 'paid' dulu (simulasi)
        $pesanan->update(['status' => 'paid']);

        $replica = new OrderFlowReplica();
        $replica->cancel($pesanan->id, $this->buyer->id, 'Salah pilih');
        $replica->rejectCancellation($pesanan->id, $this->seller->id);

        $pesanan->refresh();
        $this->assertEquals('confirmed', $pesanan->status); // paid → confirmed setelah reject
        $this->assertTrue($pesanan->is_cancellation_rejected);
    }

    /** @test */
    public function non_seller_cannot_update_order_status()
    {
        $this->checkout_success_with_midtrans();
        $pesanan = Pesanan::first();

        $otherUser = User::factory()->create();
        $replica = new OrderFlowReplica();

        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $otherUser->id,
            status: 'packed'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Unauthorized', $result['message']);
    }

    /** @test */
    public function cannot_cancel_shipped_order()
    {
        $this->checkout_success_with_midtrans();
        $pesanan = Pesanan::first();

        // Kirim dulu
        $replica = new OrderFlowReplica();
        $replica->updateStatus($pesanan->id, $this->seller->id, 'shipped');

        // Coba cancel
        $result = $replica->cancel(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            alasan: 'Terlambat'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak dapat dibatalkan', $result['message']);
    }
}
