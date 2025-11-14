<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Api\PcBuildController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest')->name('api.register');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/products', [ProductPageController::class, 'index']);
Route::get('/productAll', [PcBuildController::class, 'products']);
Route::get('/products/{id_produk}', [ProductPageController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    // Rute 'logout' harus di dalam sini
    Route::post('/logout', [LoginController::class, 'logout']);
    // Rute untuk cek user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('manage')->name('manage.')->group(function () {
        Route::apiResource('pcBuild', PcBuildController::class);
    });
});
