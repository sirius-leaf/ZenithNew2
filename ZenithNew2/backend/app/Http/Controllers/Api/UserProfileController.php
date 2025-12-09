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
     *
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Get current user profile",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('toko'));
    }

    /**
     * Update profil user (nama, nomor telepon, alamat)
     *
     * @OA\Post(
     *     path="/api/profile/update",
     *     tags={"Profile"},
     *     summary="Update user profile",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="no_telpon", type="string"),
     *                 @OA\Property(property="alamat", type="string"),
     *                 @OA\Property(property="profile_photo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated",
     *         @OA\JsonContent(type="object")
     *     )
     * )
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
     *
     * @OA\Put(
     *     path="/api/profile/update-password",
     *     tags={"Profile"},
     *     summary="Update password",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password"),
     *             @OA\Property(property="new_password", type="string", format="password"),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password berhasil diperbarui")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
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
     *
     * @OA\Post(
     *     path="/api/profile/store/update",
     *     tags={"Profile"},
     *     summary="Update store profile",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="store_name", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="store_photo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store profile updated",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
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
