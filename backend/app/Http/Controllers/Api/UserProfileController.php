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
            'name' => 'sometimes|string|max:255',
            'no_telpon' => 'sometimes|string|max:20',
            'alamat' => 'sometimes|string|max:255',
            'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'no_telpon', 'alamat']);

        // Handle Photo Upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && \Illuminate\Support\Facades\Storage::exists('public/' . $user->profile_photo)) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $user->profile_photo);
            }

            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
        }

        $user->update($data);

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
            'new_password' => 'required|min:6|confirmed',
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
    /**
     * Update profil toko (nama toko, alamat, deskripsi, foto toko)
     */
    public function updateStoreProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'penjual') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'store_name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'required|string',
            'store_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['store_name', 'address', 'description']);

        // Handle Store Photo Upload
        if ($request->hasFile('store_photo')) {
            // Delete old photo if exists
            if ($user->store_photo && \Illuminate\Support\Facades\Storage::exists('public/' . $user->store_photo)) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $user->store_photo);
            }

            $path = $request->file('store_photo')->store('store_photos', 'public');
            $data['store_photo'] = $path;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil toko berhasil diperbarui',
            'user' => $user
        ]);
    }
}
