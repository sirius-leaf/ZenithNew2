<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{


    public function login(Request $request)
    {
        // 1. Validasi (Ini sama, sudah bagus)
        // Jika validasi gagal, Laravel otomatis kirim respon JSON 422
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Cek Otentikasi (Diganti dari Auth::attempt)
        $user = User::where('email', $request->email)->first();

        // Cek jika user ada DAN password-nya benar
        if (!$user || !Hash::check($request->password, $user->password)) {

            // 3. Respon Gagal (Diganti dari throw ValidationException)
            // Kirim respon JSON 401 (Unauthorized)
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // 4. Respon Sukses (Diganti dari redirect)
        // Hapus token lama jika ada (opsional, tapi bagus)
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('api-token-untuk-' . $user->name)->plainTextToken;

        // Kirim respon JSON 200 (OK)
        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // 1. Logika Logout (Diganti dari Auth::logout())
        // Ini akan menghapus token yang dipakai untuk request ini
        // Method ini HARUS diproteksi middleware 'auth:sanctum' di file route
        $request->user()->currentAccessToken()->delete();

        // 2. Respon Sukses (Diganti dari redirect)
        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
