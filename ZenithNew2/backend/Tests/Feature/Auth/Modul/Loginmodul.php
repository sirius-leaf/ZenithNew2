<?php

namespace Tests\Feature\Auth\Modul;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class Loginmodul
{
    // Perhatikan: Parameter ke-2 (RecaptchaService) SUDAH DIHAPUS
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            // 'recaptcha' => ... (DIHAPUS)
        ]);

        // Logika Cek Recaptcha (DIHAPUS)

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        $user = Auth::user();

        // Cek banned (Tetap dipertahankan karena keamanan dasar, opsional)
        if ($user->is_banned) {
            return response()->json([
                'message' => 'Maaf, akun anda dibatasi.',
                'banned' => true
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user->only('id', 'name', 'email', 'role'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }
}
