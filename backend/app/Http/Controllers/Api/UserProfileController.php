<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{
    /**
     * Ambil data profil user yang sedang login
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Update profil user (nama, nomor telepon, alamat)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'no_telpon' => 'sometimes|string|max:20',
            'alamat'    => 'sometimes|string|max:255',
        ]);

        $user->update($request->only(['name', 'no_telpon', 'alamat']));

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user
        ]);
    }

    /**
     * Update password user
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        // cek apakah password lama benar
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah.'
            ], 422);
        }

        // update password baru
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui'
        ]);
    }
}
