<?php

namespace Tests\Unit\Checkout;

require_once __DIR__ . '/Module/CheckoutAndPayment.php';
require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../SingleModul/AddToCart.php';

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Variant;
use App\Models\Product;
use App\Models\Toko;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Tests\Unit\Checkout\Module\CheckoutAndPayment;
use CodeTests\SingleModul\AddToCart;

class CheckoutAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $seller;
    protected $toko;
    protected $product;
    protected $variant;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Data (sekali untuk setiap test)
        $this->buyer = User::factory()->create();
        $this->actingAs($this->buyer);

        $this->seller = User::factory()->create();

        $this->toko = Toko::factory()->create([
            'id_user' => $this->seller->id,
            'toko_name' => 'Seller Store',
            'deskripsi' => 'Selling good stuff'
        ]);

        $this->product = Product::factory()->create([
            'id_toko' => $this->toko->id
        ]);

        $this->variant = Variant::factory()->create([
            'id_produk' => $this->product->id_produk,
            'stok' => 50,
            'harga' => 10000
        ]);
    }

    public function test_tabel_pesanan_terisi_data_checkout()
    {
        // 2. Setup Cart in Session
        // Format: [id_varian => ['kuantitas' => x]]
        $addToCartController = new AddToCart();
        $addRequest = new \Illuminate\Http\Request();
        $addRequest->merge([
            'id_varian' => $this->variant->id_varian,
            'kuantitas' => 2
        ]);
        $addToCartController->add($addRequest, $this->variant->id_varian);

        // 3. Test Checkout
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'payment_method' => 'transfer',
            'address' => 'Jl. Test No. 123'
        ]);
        $request->setLaravelSession(session()->driver());

        $controller = new CheckoutAndPayment();
        $response = $controller->checkout($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Checkout berhasil. Silakan lakukan pembayaran.', $response->getData(true)['message']);
        
        // Assert Detail Pesanan
        $order = Pesanan::where('user_id', $this->buyer->id)->first();
        $this->assertNotNull($order);
    }

    public function test_tabel_pesanan_terisi_sesuai_data_produk()
    {
        // 2. Setup Cart in Session
        // Format: [id_varian => ['kuantitas' => x]]
        $addToCartController = new AddToCart();
        $addRequest = new \Illuminate\Http\Request();
        $addRequest->merge([
            'id_varian' => $this->variant->id_varian,
            'kuantitas' => 2
        ]);
        $addToCartController->add($addRequest, $this->variant->id_varian);

        // 3. Test Checkout
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'payment_method' => 'transfer',
            'address' => 'Jl. Test No. 123'
        ]);
        $request->setLaravelSession(session()->driver());

        $controller = new CheckoutAndPayment();
        $response = $controller->checkout($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Checkout berhasil. Silakan lakukan pembayaran.', $response->getData(true)['message']);
        
        // Assert Database Order Created
        $this->assertDatabaseHas('pesanans', [
            'user_id' => $this->buyer->id,
            'toko_id' => $this->toko->id,
            'total_harga' => 20000, // 2 * 10000
            'status' => 'pending',
            'alamat_pengiriman' => 'Jl. Test No. 123'
        ]);

        // Assert Detail Pesanan
        $order = Pesanan::where('user_id', $this->buyer->id)->first();
        $this->assertNotNull($order);
        
        $this->assertDatabaseHas('detail_pesanans', [
            'pesanan_id' => $order->id,
            'id_varian' => $this->variant->id_varian,
            'kuantitas' => 2,
            'harga' => 10000
        ]);
    }
    
    public function test_status_pesanan_diupdate_sesuai_status_pembayaran()
    {
        // 2. Setup Cart in Session
        // Format: [id_varian => ['kuantitas' => x]]
        $addToCartController = new AddToCart();
        $addRequest = new \Illuminate\Http\Request();
        $addRequest->merge([
            'id_varian' => $this->variant->id_varian,
            'kuantitas' => 2
        ]);
        $addToCartController->add($addRequest, $this->variant->id_varian);

        // 3. Test Checkout
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'payment_method' => 'transfer',
            'address' => 'Jl. Test No. 123'
        ]);
        $request->setLaravelSession(session()->driver());

        $controller = new CheckoutAndPayment();
        $response = $controller->checkout($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Checkout berhasil. Silakan lakukan pembayaran.', $response->getData(true)['message']);
        
        $paymentRequest = new \Illuminate\Http\Request();
        
        $order = Pesanan::where('user_id', $this->buyer->id)->first();
        $paymentResponse = $controller->processPayment($paymentRequest, $order->id);

        $this->assertDatabaseHas('pesanans', [
            'id' => $order->id,
            'status' => 'paid'
        ]);
    }
    
    public function test_checkout_gagal_bila_keranjang_kosong()
    {
        // 3. Test Checkout
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'payment_method' => 'transfer',
            'address' => 'Jl. Test No. 123'
        ]);
        $request->setLaravelSession(session()->driver());

        $controller = new CheckoutAndPayment();
        $response = $controller->checkout($request);
        
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Keranjang kosong.', $response->getData(true)['message']);
    }

    public function test_pembayaran_gagal_bila_pesanan_tidak_ada()
    {
        $paymentRequest = new \Illuminate\Http\Request();

        $controller = new CheckoutAndPayment();
        $paymentResponse = $controller->processPayment($paymentRequest, -1);

        $this->assertEquals(404, $paymentResponse->getStatusCode());
        $this->assertEquals('Pesanan tidak ditemukan.', $paymentResponse->getData(true)['message']);

        // Assert Database Order Created
        /*$this->assertDatabaseHas('pesanans', [
            'user_id' => $this->buyer->id,
            'toko_id' => $this->toko->id,
            'total_harga' => 20000, // 2 * 10000
            'status' => 'pending',
            'alamat_pengiriman' => 'Jl. Test No. 123'
        ]);

        // Assert Detail Pesanan
        $order = Pesanan::where('user_id', $this->buyer->id)->first();
        $this->assertNotNull($order);
        
        $this->assertDatabaseHas('detail_pesanans', [
            'pesanan_id' => $order->id,
            'id_varian' => $this->variant->id_varian,
            'kuantitas' => 2,
            'harga' => 10000
        ]);*/
        

        // Assert Valid Response
        /*$this->assertEquals(201, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('success', $responseData['status']);*/
        

        // Assert Cart Cleared
        /*$this->assertEmpty(session('cart'));*/


        // 4. Test Payment Processing
        // Simulate "Bayar" button click -> calls processPayment
        
        /*$this->assertEquals(200, $paymentResponse->getStatusCode());
        $paymentData = $paymentResponse->getData(true);
        $this->assertEquals('success', $paymentData['status']);*/

        // Assert Status Updated to Paid
        
        // Check Model Update (Fresh)
        /*$order->fresh();
        $this->assertEquals('paid', $order->status);*/
    }
}
