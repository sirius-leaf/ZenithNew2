<?php

namespace Tests\SingleModulTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Review;
use App\Models\DetailPesanan;
use App\Models\Variant;
use App\Models\Toko;
use App\Models\User;
use CodeTests\SingleModul\ProductDetail;

require_once __DIR__ . '/../SingleModul/ProductDetail.php';
require_once __DIR__ . '/../TestCase.php';

class ProductDetailTest extends TestCase
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

        $controller = new ProductDetail();
        $response = $controller->show($product->id_produk);

        $this->assertEquals(201, $response->getStatusCode());
        $data = $response->getData(true);

        $this->assertEquals($product->id_produk, $data['data']['id_produk']);
        $this->assertEquals(4.5, $data['rating']['rata-rata']);
        $this->assertEquals(2, $data['rating']['jumlah']);
        $this->assertEquals(2, $data['rating']['terjual']);
    }
}
