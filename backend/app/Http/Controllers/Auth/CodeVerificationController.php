<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;

class CodeVerificationController extends Controller
{
    /**
     * Verify the user's email using the code.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi.']);
        }

        if ($user->verification_code !== $request->code) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 400);
        }

        if ($user->verification_code_expires_at < now()) {
            return response()->json(['message' => 'Kode verifikasi kadaluarsa.'], 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            // Clear the code
            $user->verification_code = null;
            $user->verification_code_expires_at = null;
            $user->save();
        }

        return response()->json(['message' => 'Email berhasil diverifikasi!']);
    }
}
