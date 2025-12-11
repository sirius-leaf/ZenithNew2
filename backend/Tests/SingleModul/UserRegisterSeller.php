<?php

namespace CodeTests\SingleModul;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserRegisterSeller
{
    /**
     * User mengajukan permintaan menjadi penjual
     * Replika dari UserRoleController::requestSeller
     */
    public function requestSeller(Request $request)
    {
        $user = Auth::user(); // Atau $request->user();

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
}
