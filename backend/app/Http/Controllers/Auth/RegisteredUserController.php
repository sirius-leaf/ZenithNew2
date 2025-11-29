<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Services\RecaptchaService;
use App\Notifications\VerifyEmailSpaNotification;

class RegisteredUserController extends Controller
{
    public function store(Request $request, RecaptchaService $recaptcha)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password'  => ['required', Rules\Password::defaults()],
            'recaptcha' => 'required'
        ]);

        // VALIDASI RECAPTCHA
        if (!$recaptcha->verify($request->recaptcha)) {
            return response()->json([
                'message' => 'Recaptcha verification failed.'
            ], 422);
        }

        // BUAT USER
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        // KIRIM EMAIL VERIFIKASI
        $user->notify(new VerifyEmailSpaNotification());

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan login.',
            'user'    => $user
        ], 201);
    }
}
