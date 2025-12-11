<?php

namespace Tests\SingleModulTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Variant;
use App\Models\Product;
use App\Models\Toko;
use App\Models\User;
use CodeTests\SingleModul\AddToCart;

require_once __DIR__ . '/../SingleModul/AddToCart.php';
require_once __DIR__ . '/../TestCase.php';

class AddToCartTest extends TestCase
{
    use RefreshDatabase;

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

        $controller = new AddToCart();
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

        $controller = new AddToCart();
        $response = $controller->add($request, $variant->id_varian);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue(session()->has('error'));
        $this->assertFalse(session()->has('cart')); // Cart should be empty or unchanged
    }
}
