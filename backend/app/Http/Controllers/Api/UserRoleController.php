<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserRoleController extends Controller
{
    /**
     * User mengajukan permintaan menjadi penjual (API)
     */
    public function requestSeller(Request $request)
    {
        $user = $request->user();

        // Validasi agar tidak request berulang
        if ($user->role === 'penjual_pending') {
            return response()->json(['message' => 'Permintaan Anda sedang diproses.'], 409);
        }
        if ($user->role === 'penjual' || $user->role === 'admin') {
            return response()->json(['message' => 'Anda sudah memiliki akses penjual/admin.'], 409);
        }

        $user->update(['role' => 'penjual_pending']);

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan menjadi penjual berhasil dikirim. Tunggu konfirmasi admin.',
            'data' => $user
        ], 200);
    }

    /**
     * Admin melihat semua request penjual (API)
     */
    public function index()
    {
        // Mengambil user dengan role 'penjual_pending'
        $sellerRequests = User::where('role', 'penjual_pending')->get();

        return response()->json([
            'status' => 'success',
            'data' => $sellerRequests
        ], 200);
    }

    /**
     * Admin menyetujui permintaan user menjadi penjual (API)
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        // Pastikan yang diapprove memang sedang pending
        if ($user->role !== 'penjual_pending') {
             return response()->json(['message' => 'Status user tidak valid untuk disetujui.'], 400);
        }

        $user->update(['role' => 'penjual']);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil disetujui menjadi penjual.',
            'data' => $user
        ], 200);
    }
}
