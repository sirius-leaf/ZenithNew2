<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    // ... existing methods ...

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