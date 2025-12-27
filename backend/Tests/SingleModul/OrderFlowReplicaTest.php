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
    public function checkout_berhasil_dengan_midtrans()
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
    public function checkout_berhasil_dengan_cod()
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
    public function checkout_gagal_jika_stok_tidak_mencukupi()
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
    public function penjual_dapat_mengubah_status_pesanan_menjadi_dikemas()
    {
        $this->checkout_berhasil_dengan_midtrans();
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
    public function pembeli_dapat_menyelesaikan_pesanan_cod()
    {
        $this->checkout_berhasil_dengan_cod();
        $pesanan = Pesanan::first();

        // Penjual kirim dulu
        $replica = new OrderFlowReplica();
        $replica->updateStatus($pesanan->id, $this->seller->id, 'shipped');

        // Pembeli terima
        $result = $replica->updateStatus(
            orderId: $pesanan->id,
            userId: $this->buyer->id,
            status: 'completed'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('completed', $pesanan->fresh()->status);
    }

    /** @test */
    public function bukan_penjual_tidak_dapat_mengubah_status_pesanan()
    {
        $this->checkout_berhasil_dengan_midtrans();
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
}
