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
     *
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","recaptcha"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="recaptcha", type="string", example="token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login berhasil!"),
     *             @OA\Property(property="token", type="string", example="1|laravel_sanctum_token"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=422, description="Recaptcha verification failed")
     * )
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
     *
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout berhasil.")
     *         )
     *     )
     * )
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