<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * API: Menampilkan daftar user (JSON).
     * Query params: ?page=1&search=&per_page=5
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);
        $search = trim($request->input('search', ''));

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    /**
     * API: Update role user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:admin,penjual,user',
        ]);

        $user->update([
            'role' => $request->role,
        ]);

        return response()->json($user);
    }

    /**
     * API: Hapus user.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
            'id' => $user->id
        ]);
    }

    // 🔒 Tambahkan method berikut — INI YANG ANDA PERLUKAN
    /**
     * API: Ban user.
     */
    public function ban(User $user): JsonResponse
    {
        $user->update(['is_banned' => true]);

        return response()->json([
            'message' => 'User banned successfully',
            'user' => $user
        ]);
    }

    /**
     * API: Unban user.
     */
    public function unban(User $user): JsonResponse
    {
        $user->update(['is_banned' => false]);

        return response()->json([
            'message' => 'User unbanned successfully',
            'user' => $user
        ]);
    }
}