<?php

namespace Tests\SingleModul;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Toko;
use App\Models\Product;
use App\Models\Variant;

class CheckoutReplicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_success()
    {
        // Setup
        $user = User::factory()->create();
        $toko = Toko::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['toko_id' => $toko->id]);
        $variant = Variant::factory()->create([
            'id_produk' => $product->id_produk,
            'stok' => 5,
            'harga' => 100000
        ]);

        $cart = [$variant->id_varian => ['kuantitas' => 2]];

        // Jalankan replika
        $replica = new CheckoutReplica();
        $result = $replica->process($cart, $user->id, 'Jl. Test');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('pesanans', [
            'user_id' => $user->id,
            'total_harga' => 200000
        ]);
        $this->assertDatabaseHas('variants', [
            'id_varian' => $variant->id_varian,
            'stok' => 3
        ]);
    }
}
