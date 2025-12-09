<?php

namespace Tests\GroupedModulTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Review;
use App\Models\DetailPesanan;
use App\Models\Variant;
use App\Models\Toko;
use App\Models\User;
use CodeTests\GroupedModul\ProductGroup;

require_once __DIR__ . '/../GroupedModul/ProductGroup.php';
require_once __DIR__ . '/../TestCase.php';

class ProductGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_product_detail()
    {
        $user = User::factory()->create();
        $toko = Toko::factory()->create([
            'id_user' => $user->id,
            'toko_name' => 'Test Store',
            'deskripsi' => 'Test Description'
        ]);
        $product = Product::factory()->create(['id_toko' => $toko->id]);
        $variant = Variant::factory()->create(['id_produk' => $product->id_produk]);

        // Create reviews
        Review::factory()->create(['id_produk' => $product->id_produk, 'rating' => 5, 'id_user' => $user->id]);
        Review::factory()->create(['id_produk' => $product->id_produk, 'rating' => 4, 'id_user' => $user->id]);

        // Create detail pesanan (sales)
        DetailPesanan::factory()->create(['id_varian' => $variant->id_varian, 'kuantitas' => 2]);

        $controller = new ProductGroup();
        $response = $controller->show($product->id_produk);

        $this->assertEquals(201, $response->getStatusCode());
        $data = $response->getData(true);

        $this->assertEquals($product->id_produk, $data['data']['id_produk']);
        $this->assertEquals(4.5, $data['rating']['rata-rata']);
        $this->assertEquals(2, $data['rating']['jumlah']);
        $this->assertEquals(2, $data['rating']['terjual']);
    }

    public function test_can_add_item_to_cart()
    {
        $user = User::factory()->create();
        $toko = Toko::factory()->create([
            'id_user' => $user->id,
            'toko_name' => 'Test Store',
            'deskripsi' => 'Test Description'
        ]);
        $product = Product::factory()->create(['id_toko' => $toko->id]);
        $variant = Variant::factory()->create(['id_produk' => $product->id_produk, 'stok' => 10]);

        $request = new \Illuminate\Http\Request();
        $request->merge(['kuantitas' => 2]);
        $request->setLaravelSession(session()->driver());

        $controller = new ProductGroup();
        $response = $controller->add($request, $variant->id_varian);

        $this->assertEquals(302, $response->getStatusCode()); // Redirect back

        $cart = session('cart');
        $this->assertArrayHasKey($variant->id_varian, $cart);
        $this->assertEquals(2, $cart[$variant->id_varian]['kuantitas']);
    }

    public function test_cannot_add_more_than_stock()
    {
        $user = User::factory()->create();
        $toko = Toko::factory()->create([
            'id_user' => $user->id,
            'toko_name' => 'Test Store',
            'deskripsi' => 'Test Description'
        ]);
        $product = Product::factory()->create(['id_toko' => $toko->id]);
        $variant = Variant::factory()->create(['id_produk' => $product->id_produk, 'stok' => 5]);

        $request = new \Illuminate\Http\Request();
        $request->merge(['kuantitas' => 10]);
        $request->setLaravelSession(session()->driver());

        $controller = new ProductGroup();
        $response = $controller->add($request, $variant->id_varian);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue(session()->has('error'));
        $this->assertFalse(session()->has('cart')); // Cart should be empty or unchanged
    }
}
