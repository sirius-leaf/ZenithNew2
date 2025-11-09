<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VariantController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\TokoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('manage')->name('manage.')->group(function () {
    Route::resource('produk', ProductController::class);
});



// Logout route di luar group
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::prefix('manage')->name('manage.')->group(function () {
        Route::resource('user', UserController::class);
        Route::get('/tokomu', [TokoController::class, 'show'])->name('show');
        Route::resource('toko', TokoController::class);
        Route::post('/becoume-seller', [UserRoleController::class, 'requestSeller'])->name('user.requestSeller');

        // admin halaman konfirmasi
        Route::get('/admin/seller-requests', [UserRoleController::class, 'index'])->middleware('can:is-admin')->name('admin.sellerRequests');
        Route::post('/admin/seller-requests/{user}/approve', [UserRoleController::class, 'approve'])->middleware('can:is-admin')->name('admin.sellerRequests.approve');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});


require __DIR__ . '/auth.php';
require __DIR__ . '/api.php';
