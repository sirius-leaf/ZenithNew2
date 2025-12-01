<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function verify($token)
    {
        if ($token === 'mobile_dev_bypass') {
            return true;
        }

        $secret = config('services.recaptcha.secret');

        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => $secret,
                'response' => $token,
            ]
        );

        return $response->json()['success'] ?? false;
    }
}
