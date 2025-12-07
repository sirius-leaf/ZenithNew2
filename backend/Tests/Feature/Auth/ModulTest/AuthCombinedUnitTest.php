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

class AuthCombinedUnitTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registrasi_berhasil()
    {
        $data = [
            'name' => 'Mahasiswa Baru',
            'email' => 'mahasiswa@example.com',
            'password' => 'password123',
        ];

        // Request tanpa header Recaptcha/Email verification
        $request = Request::create('/api/register', 'POST', $data);
        $request->headers->set('Accept', 'application/json');

        // Tidak perlu Mail::fake() lagi

        $controller = new Registermodul();
        $response = $controller->store($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('users', ['email' => 'mahasiswa@example.com']);
    }

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

    #[Test]
    public function registrasi_dan_login_dengan_logika_kondisional()
    {
        $emailRegister = 'asli@example.com';
        $emailLogin    = 'beda@gmail.com';
        $password      = 'password123';

        // --- STEP 1: REGISTRASI ---
        $regRequest = Request::create('/api/register', 'POST', [
            'name' => 'User Test',
            'email' => $emailRegister,
            'password' => $password,
        ]);
        $regRequest->headers->set('Accept', 'application/json');

        $regController = new Registermodul();
        $regResponse = $regController->store($regRequest);

        $this->assertEquals(201, $regResponse->getStatusCode());

        // --- STEP 2: LOGIN ---
        $loginRequest = Request::create('/api/login', 'POST', [
            'email' => $emailLogin,
            'password' => $password,
        ]);
        $loginRequest->headers->set('Accept', 'application/json');

        $loginController = new Loginmodul();
        // Login langsung, tanpa inject service
        $loginResponse = $loginController->login($loginRequest);

        // --- STEP 3: ASSERT ---
        if ($emailLogin !== $emailRegister) {
            $this->assertEquals(401, $loginResponse->getStatusCode());
        } else {
            $this->assertEquals(200, $loginResponse->getStatusCode());
        }
    }
}
