<?php

namespace Tests\SingleModul;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Toko;
use App\Models\Product;
use App\Models\Variant;
use Tests\SingleModul\CheckoutReplica; // Pastikan ini ada

class CheckoutReplicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_success()
    {
        // Buat user terpisah: buyer dan seller
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        // Buat toko milik seller
        $toko = Toko::factory()->create(['id_user' => $seller->id]);

        // Buat produk di toko tersebut
        $product = Product::factory()->create(['id_toko' => $toko->id]);

        // Buat varian produk
        $variant = Variant::factory()->create([
            'id_produk' => $product->id_produk,
            'stok' => 5,
            'harga' => 100000
        ]);

        // Siapkan keranjang
        $cart = [$variant->id_varian => ['kuantitas' => 2]];

        // Jalankan proses checkout
        $replica = new CheckoutReplica();
        $result = $replica->process($cart, $buyer->id, 'Jl. Test');

        // Jika gagal, tampilkan pesan error lengkap
        if (!$result['success']) {
            $this->fail('Checkout gagal: ' . ($result['message'] ?? 'Tidak ada pesan error'));
        }

        // Pastikan sukses
        $this->assertTrue($result['success']);

        // (Opsional) Cek data di database
        $this->assertDatabaseHas('pesanans', [
            'user_id' => $buyer->id,
            'toko_id' => $toko->id,
            'total_harga' => 200000,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('variants', [
            'id_varian' => $variant->id_varian,
            'stok' => 3 // 5 - 2
        ]);
    }
}
