<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest')->name('api.register');

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Rute 'logout' harus di dalam sini
    Route::post('/logout', [LoginController::class, 'logout']);
    // Rute untuk cek user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Tambahkan rute API Anda yang lain di sini...

});
