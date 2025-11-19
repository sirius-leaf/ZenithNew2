<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Api\TokoController;
use App\Http\Controllers\MyTokoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PcBuildController;
use App\Http\Controllers\Api\VariantController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\MyTokoProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/buy', [ProductPageController::class, 'index'])->name('home');

// Rute untuk Halaman Detail Produk
// Gunakan {id_produk} sesuai dengan Primary Key Anda
Route::get('/product/{id_produk}', [ProductPageController::class, 'show'])->name('shop.show');

Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Logout route di luar group
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::prefix('manage')->name('manage.')->group(function () {
        Route::resource('user', UserController::class)->middleware('can:is-admin');
        Route::resource('produk', ProductController::class);
        Route::resource('pcBuild', PcBuildController::class);
        // Route::get('/tokomu', [MyTokoController::class, 'show'])->name('show');
        Route::resource('toko', TokoController::class);
        Route::post('/become-seller', [UserRoleController::class, 'requestSeller'])->name('user.requestSeller');

        // admin halaman konfirmasi
        Route::get('/admin/seller-requests', [UserRoleController::class, 'index'])->middleware('can:is-admin')->name('admin.sellerRequests');
        Route::post('/admin/seller-requests/{user}/approve', [UserRoleController::class, 'approve'])->middleware('can:is-admin')->name('admin.sellerRequests.approve');
    });



    Route::prefix('toko-saya')->name('toko.')->group(function () {
        // Halaman utama kelola toko (menampilkan daftar produk)
        Route::get('/', [MyTokoController::class, 'show'])->name('show');

        Route::resource('produk', MyTokoProductController::class)
            ->except(['index', 'show'])
            ->parameters(['produk' => 'id'])
            // GANTI INI:
            // ->middleware(function ($request, $next) { ... });

            // MENJADI INI (nama string):
            ->middleware('isSellerWithToko'); // <-- Jauh lebih bersih!

    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    // ... (Rute dashboard, dll)

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');

    // Rute untuk memproses pesanan
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // (Opsional) Halaman sukses
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
});

// Rute untuk Keranjang Belanja (Cart)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id_varian}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id_varian}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id_varian}', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});


require __DIR__ . '/auth.php';
require __DIR__ . '/api.php';
