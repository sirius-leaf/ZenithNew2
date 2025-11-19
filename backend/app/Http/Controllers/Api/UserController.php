<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user (API).
     */
    public function index()
    {
        // Mengambil semua user
        $users = User::all();

        // Return JSON
        return response()->json([
            'status' => 'success',
            'data' => $users
        ], 200);
    }

    /**
     * Update role user (API).
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:admin,penjual,user',
        ]);

        $user->update([
            'role' => $request->role,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role user berhasil diubah!',
            'data' => $user
        ], 200);
    }

    /**
     * Hapus user (API).
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil dihapus!'
        ], 200);
    }
}
