<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    /**
     * Mengajukan permintaan menjadi seller.
     */
    public function requestSeller(Request $request): JsonResponse
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $user = $request->user();

        // ✅ Simpan data ke database
        $user->update([
            'role' => 'user',
            'is_seller_requesting' => true,
            'store_name' => $request->store_name,
            'address' => $request->address,
            'description' => $request->description,
        ]);

        // 📝 Tambahkan log untuk debugging
        \Log::info('User submitted seller request', [
            'user_id' => $user->id,
            'store_name' => $request->store_name,
            'address' => $request->address,
            'description' => $request->description,
            'is_seller_requesting' => $user->is_seller_requesting
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan menjadi seller berhasil diajukan!',
            'user' => $user
        ]);
    }

    /**
     * Menampilkan daftar permintaan seller (untuk admin).
     */
    public function index(): JsonResponse
    {
        $sellers = User::where('is_seller_requesting', true)
            ->select('id', 'name', 'email', 'store_name', 'address', 'description')
            ->get();

        return response()->json($sellers);
    }

    /**
     * Menyetujui permintaan seller → ubah role jadi 'penjual'.
     */
    public function approve(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!$user->is_seller_requesting) {
            return response()->json([
                'success' => false,
                'message' => 'User ini bukan dalam status pending.'
            ], 400);
        }

        $user->update([
            'role' => 'penjual',
            'is_seller_requesting' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Seller berhasil disetujui!',
            'user' => $user
        ]);
    }

    /**
     * Menolak permintaan seller → reset flag.
     */
    public function reject(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!$user->is_seller_requesting) {
            return response()->json([
                'success' => false,
                'message' => 'User ini bukan dalam status pending.'
            ], 400);
        }

        $user->update([
            'is_seller_requesting' => false,
            'store_name' => null,
            'address' => null,
            'description' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan seller ditolak.',
            'user' => $user
        ]);
    }
}