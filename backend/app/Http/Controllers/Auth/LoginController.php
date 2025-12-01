<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

use App\Services\RecaptchaService;

class LoginController extends Controller
{
    /**
     * Handle API login request with Sanctum token.
     */
    public function login(Request $request, RecaptchaService $recaptchaService): JsonResponse
    {
        // Validasi input
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            // 'recaptcha' => ['required'],
        ]);

        //
        // if (!$recaptchaService->verify($request->recaptcha)) {
        //     return response()->json([
        //         'message' => 'Recaptcha verification failed.'
        //     ], 422);
        // }

        // ✅ Gunakan Auth::attempt() agar kompatibel dengan Sanctum & middleware
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $user = Auth::user();

        // 🔒 Cek banned
        if ($user->is_banned) {
            return response()->json([
                'message' => 'Maaf, akun anda dibatasi. Mohon hubungi admin untuk masalah ini.',
                'banned' => true
            ], 403);
        }

        // ✅ Hapus token lama (opsional tapi aman — hindari token menumpuk)
        $user->tokens()->delete();

        // ✅ Buat token Sanctum (Personal Access Token)
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user->only('id', 'name', 'email', 'role', 'store_name'), // aman: jangan kirim password, dll
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