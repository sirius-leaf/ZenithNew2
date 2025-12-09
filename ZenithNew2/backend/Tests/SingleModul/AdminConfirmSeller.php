<?php

namespace CodeTests\SingleModul;

use App\Models\User;
use Illuminate\Http\Request;

class AdminConfirmSeller
{
    /**
     * Admin menyetujui permintaan user menjadi penjual
     * Replika dari UserRoleController::approve
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
        // Asumsi relasi user ke toko adalah hasOne
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
}
