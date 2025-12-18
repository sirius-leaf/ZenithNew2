<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Tests\Feature\Auth\Modul\AuthModul; // <--- CUKUP SATU IMPORT INI
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

        $request = Request::create('/api/register', 'POST', $data);
        $request->headers->set('Accept', 'application/json');

        // INSTANSIASI MODUL GABUNGAN
        $modul = new AuthModul();
        // Panggil method 'register' (bukan store lagi, biar lebih jelas)
        $response = $modul->register($request);

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
        ];

        $request = Request::create('/api/login', 'POST', $loginData);
        $request->headers->set('Accept', 'application/json');

        // INSTANSIASI MODUL GABUNGAN
        $modul = new AuthModul();
        $response = $modul->login($request);

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

        // Pakai AuthModul
        $modul = new AuthModul();
        $regResponse = $modul->register($regRequest);

        $this->assertEquals(201, $regResponse->getStatusCode());

        // --- STEP 2: LOGIN ---
        $loginRequest = Request::create('/api/login', 'POST', [
            'email' => $emailLogin,
            'password' => $password,
        ]);
        $loginRequest->headers->set('Accept', 'application/json');

        // Pakai AuthModul (Instance baru atau pakai yang sama juga bisa)
        $modulLogin = new AuthModul();
        $loginResponse = $modulLogin->login($loginRequest);

        // --- STEP 3: ASSERT ---
        if ($emailLogin !== $emailRegister) {
            $this->assertEquals(401, $loginResponse->getStatusCode());
        } else {
            $this->assertEquals(200, $loginResponse->getStatusCode());
        }
    }
}
