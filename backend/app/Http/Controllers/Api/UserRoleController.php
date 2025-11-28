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

        // Validasi Input
        $request->validate([
            'store_name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'required|string',
            'ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'npwp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Upload File
        $ktpPath = null;
        if ($request->hasFile('ktp')) {
            $ktpPath = $request->file('ktp')->store('documents/ktp', 'public');
        }

        $npwpPath = null;
        if ($request->hasFile('npwp')) {
            $npwpPath = $request->file('npwp')->store('documents/npwp', 'public');
        }

        // Update User Data
        $user->update([
            'role' => 'penjual_pending',
            'store_name' => $request->store_name,
            'address' => $request->address,
            'description' => $request->description,
            'ktp_path' => $ktpPath,
            'npwp_path' => $npwpPath,
        ]);

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
        // Mengambil user dengan role 'penjual_pending' dengan pagination
        $sellerRequests = User::where('role', 'penjual_pending')->paginate(3);

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

        // Create Toko record
        if (!$user->toko) {
            $user->toko()->create([
                'toko_name' => $user->store_name,
                'deskripsi' => $user->description,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil disetujui menjadi penjual.',
            'data' => $user
        ], 200);
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);

        // Pastikan yang diapprove memang sedang pending
        if ($user->role !== 'penjual_pending') {
            return response()->json(['message' => 'Status user tidak valid untuk disetujui.'], 400);
        }

        $user->update(['role' => 'user']);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil ditolak menjadi penjual.',
            'data' => $user
        ], 200);
    }
}
