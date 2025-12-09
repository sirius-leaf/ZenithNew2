<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url', 'http://localhost:5173') . '/dashboard'
            );
        }

        // Redirect to frontend dashboard where the verification warning is displayed
        return redirect(config('app.frontend_url', 'http://localhost:5173') . '/dashboard');
    }
}
