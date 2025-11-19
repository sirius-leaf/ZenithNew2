<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokoController extends Controller
{
    /**
     * Cek status toko user saat ini.
     * Berguna untuk frontend mengetahui apakah user sudah punya toko atau belum.
     */
    public function index()
    {
        $user = Auth::user();

        // Cek apakah user punya toko
        $toko = $user->toko; // Asumsi relasi 'toko' di model User adalah hasOne

        if ($toko) {
            return response()->json([
                'status' => 'exists',
                'data' => $toko
            ], 200);
        }

        return response()->json([
            'status' => 'empty',
            'message' => 'User belum memiliki toko.'
        ], 200);
    }

    /**
     * Menyimpan Toko Baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Cek Role (Harus Penjual)
        if ($user->role !== 'penjual') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya akun Penjual yang bisa membuat toko.'
            ], 403); // 403 Forbidden
        }

        // 2. Cek apakah user SUDAH punya toko
        if ($user->toko) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah memiliki toko, tidak bisa membuat baru.'
            ], 409); // 409 Conflict
        }

        // 3. Validasi Input
        $request->validate([
            'toko_name' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
        ]);

        // 4. Buat Toko
        $toko = $user->toko()->create([
            'toko_name' => $request->toko_name,
            'deskripsi' => $request->deskripsi,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Toko berhasil dibuat!',
            'data' => $toko
        ], 201); // 201 Created
    }
}
