<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Variant;
use App\Http\Controllers\Api\TokoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PcBuildController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rute umum (tidak perlu autentikasi)
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest')->name('api.register');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/products', [ProductPageController::class, 'index']);
Route::get('/productAll', [PcBuildController::class, 'products']);
Route::get('/products/{id_produk}', [ProductPageController::class, 'show']);

// Rute yang memerlukan autentikasi (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout']);
    // Rute untuk cek user yang sedang login
    Route::post('/order/preview', [OrderController::class, 'preview']); // Untuk melihat ringkasan & cek stok
    Route::post('/order/store', [OrderController::class, 'store']);     // Untuk final checkout
    Route::post('/payment/simulate/{order_id}', [PaymentController::class, 'simulate']);

    // Dapatkan data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api.user');

    // Manajemen PC Build
    Route::prefix('manage')->name('manage.')->group(function () {
        Route::apiResource('pcBuild', PcBuildController::class);
        Route::apiResource('product', ProductController::class);
        Route::get('productToko', [ProductController::class, 'create']);
        Route::apiResource('users', UserController::class)->only(['index', 'update', 'destroy']);
        Route::apiResource('toko', TokoController::class)->only(['index', 'store']);
        // 1. User: Request jadi penjual
        Route::post('/become-seller', [UserRoleController::class, 'requestSeller']);
        // 2. Admin: Lihat list request (Perlu middleware 'can:is-admin' atau cek role di controller)
        Route::get('/admin/seller-requests', [UserRoleController::class, 'index']);
        // 3. Admin: Approve request
        Route::post('/admin/seller-requests/{id}/approve', [UserRoleController::class, 'approve']);
    });
});

Route::get('/variant/check/{id_varian}', function ($id_varian) {
    $variant = Variant::with('product')->find($id_varian);
    if (!$variant) {
        return response()->json(['exists' => false], 404);
    }
    return response()->json(['exists' => true, 'variant' => $variant], 200);
})->where('id_varian', '[0-9]+');

// 🔑 Manajemen User (Admin-only — pastikan middleware tambahan jika perlu)
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index']);           // GET /api/users
    Route::put('/{user}', [UserController::class, 'update']);   // PUT /api/users/1
    Route::delete('/{user}', [UserController::class, 'destroy']); // DELETE /api/users/1
});

// 🔒 Ban / Unban
Route::post('/users/{user}/ban', [UserController::class, 'ban']);
Route::post('/users/{user}/unban', [UserController::class, 'unban']);

// 🔐 Manajemen Role (User → Seller) — ✅ DIPINDAH KE DALAM GROUP INI
Route::prefix('role')->name('role.')->group(function () {
    Route::post('/request-seller', [UserRoleController::class, 'requestSeller']);
    Route::get('/seller-requests', [UserRoleController::class, 'index']); // untuk admin
    Route::post('/approve-seller/{id}', [UserRoleController::class, 'approve']);
    Route::post('/reject-seller/{id}', [UserRoleController::class, 'reject']);
});


