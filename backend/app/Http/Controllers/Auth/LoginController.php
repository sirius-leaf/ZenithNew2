<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Handle API login request with Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        // Validasi input
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Ambil user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // ❌ Email tidak ditemukan
        if (!$user) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // ❌ Password salah
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // ❌ CEK EMAIL BELUM DIVERIFIKASI
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email belum diverifikasi. Silakan cek email Anda.',
                'verify_required' => true,
            ], 403);
        }

        // ❌ CEK BANNED
        if ($user->is_banned) {
            return response()->json([
                'message' => 'Maaf, akun anda dibatasi. Mohon hubungi admin.',
                'banned' => true
            ], 403);
        }

        // 🔐 Hapus token lama (opsional tapi aman)
        $user->tokens()->delete();

        // 🔐 Buat token baru Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user->only('id', 'name', 'email', 'role', 'store_name'),
            'token' => $token,
        ]);
    }

    /**
     * Handle API logout request.
     */
    public function logout(Request $request): JsonResponse
    {
        // Hapus token saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.'
        ]);
    }
}
