<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\Api\PcBuildController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleController;

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
    

    // Dapatkan data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api.user');

    // Manajemen PC Build
    Route::prefix('manage')->name('manage.')->group(function () {
        Route::apiResource('pcBuild', PcBuildController::class);
    });

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
});