<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class UserRoleController extends Controller
{
    /**
     * Mengajukan permintaan menjadi seller (untuk user yang login).
     */
    public function requestSeller(Request $request): JsonResponse
    {
        // Validasi input
        $request->validate([
            'store_name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Ambil user yang sedang login
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan. Harap login kembali.'
            ], 401);
        }

        // Update data user
        $user->update([
            'role' => 'penjual_pending',
            'store_name' => $request->store_name,
            'address' => $request->address,
            'description' => $request->description,
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
        $sellers = User::where('role', 'penjual_pending')
            ->select('id', 'name', 'email', 'store_name', 'address', 'description')
            ->get();

        return response()->json($sellers);
    }

    /**
     * Menyetujui permintaan seller → ubah role jadi 'penjual'.
     */
    public function approve(int $id): JsonResponse
    {
        $user = User::find($id); // Gunakan find() agar tidak error jika tidak ditemukan

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        if ($user->role !== 'penjual_pending') {
            return response()->json([
                'success' => false,
                'message' => 'User ini bukan dalam status pending.'
            ], 400);
        }

        $user->update(['role' => 'penjual']);

        return response()->json([
            'success' => true,
            'message' => 'Seller berhasil disetujui!',
            'user' => $user
        ]);
    }

    /**
     * Menolak permintaan seller → kembalikan ke role 'user'.
     */
    public function reject(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        if ($user->role !== 'penjual_pending') {
            return response()->json([
                'success' => false,
                'message' => 'User ini bukan dalam status pending.'
            ], 400);
        }

        $user->update([
            'role' => 'user',
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