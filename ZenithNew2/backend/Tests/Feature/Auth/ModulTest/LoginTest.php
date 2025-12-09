<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

// Import Modul Replika
use Tests\Feature\Auth\Modul\Registermodul;
use Tests\Feature\Auth\Modul\Loginmodul;

use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_berhasil()
    {
        $password = 'rahasia123';
        User::factory()->create([
            'email' => 'dosen@example.com',
            'password' => Hash::make($password),
        ]);

        $loginData = [
            'email' => 'dosen@example.com',
            'password' => $password,
            // 'recaptcha' tidak perlu dikirim
        ];

        $request = Request::create('/api/login', 'POST', $loginData);
        $request->headers->set('Accept', 'application/json');

        // Tidak perlu Mock Recaptcha lagi

        $controller = new Loginmodul();
        // Panggil fungsi login cukup dengan $request saja
        $response = $controller->login($request);

        $this->assertEquals(200, $response->getStatusCode());

        $result = $response->getData(true);
        $this->assertEquals('Login berhasil!', $result['message']);
        $this->assertArrayHasKey('token', $result);
    }
}
