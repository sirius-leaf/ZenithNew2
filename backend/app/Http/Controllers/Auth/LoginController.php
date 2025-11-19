<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Handle API login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        // 🔴 Cek 1: User tidak ditemukan atau password salah
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // 🔴 Cek 2: User ditemukan & password benar, TAPI DIBANNED
        if ($user->is_banned) {
            return response()->json([
                'message' => 'Maaf, akun anda dibatasi. Mohon hubungi admin untuk masalah ini.',
                'banned' => true
            ], 403); // 403 Forbidden
        }

        // ✅ Semua valid → buat token
        $user->tokens()->delete(); // Hapus token lama (opsional tapi aman)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Handle API logout request.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}