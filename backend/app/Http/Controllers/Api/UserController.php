<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * - Jika ?role=penjual_pending → hanya pending
     * - Jika tidak ada ?role → semua user (default)
     */
    /**
     * - Jika ?role=penjual_pending → hanya pending
     * - Jika tidak ada ?role → semua user (default)
     *
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Get all users (Admin)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by role",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = trim($request->input('search', ''));
        $role = $request->input('role'); // optional

        $query = User::query()->with('toko');

        // ✅ Hanya filter jika parameter role dikirim
        if ($role) {
            $query->where('role', $role);
        }

        if ($search) {
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
    /**
     * API: Update role user.
     *
     * @OA\Put(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Update user role (Admin)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", enum={"admin","penjual","user","penjual_pending"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User role updated",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:admin,penjual,user,penjual_pending',
        ]);

        $user->update([
            'role' => $request->role,
        ]);

        return response()->json($user);
    }

    /**
     * API: Hapus user.
     */
    /**
     * API: Hapus user.
     *
     * @OA\Delete(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Delete user (Admin)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
            'id' => $user->id
        ]);
    }

    // 🔒 Ban / Unban
    /**
     * @OA\Post(
     *     path="/api/users/{user}/ban",
     *     tags={"Users"},
     *     summary="Ban user (Admin)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User banned",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function ban(User $user): JsonResponse
    {
        $user->update(['is_banned' => true]);
        return response()->json(['message' => 'User banned successfully', 'user' => $user]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{user}/unban",
     *     tags={"Users"},
     *     summary="Unban user (Admin)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User unbanned",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function unban(User $user): JsonResponse
    {
        $user->update(['is_banned' => false]);
        return response()->json(['message' => 'User unbanned successfully', 'user' => $user]);
    }
}