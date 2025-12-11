<?php

namespace Tests\Feature\Auth\Modul;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;

class Registermodul
{
    public function store(Request $request): JsonResponse
    {
        // Validasi standar
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', Rules\Password::defaults()],
        ]);

        // Simpan User (Tanpa kode verifikasi)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'verification_code' => ... (DIHAPUS)
        ]);

        // Mail::to(...) (DIHAPUS)

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan login.',
            'user' => $user
        ], 201);
    }
}
