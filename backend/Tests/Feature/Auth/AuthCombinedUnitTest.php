<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class AuthCombinedUnitTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // METHOD 1: TEST REGISTRASI SAJA (Unit)
    // ==========================================
    #[Test]
    public function RegistrasiBerhasil()
    {
        $data = [
            'name' => 'Mahasiswa Baru',
            'email' => 'mahasiswa@example.com',
            'password' => 'password123',
        ];

        $request = Request::create('/api/register', 'POST', $data);
        $request->headers->set('Accept', 'application/json');

        Mail::fake();

        $controller = new RegisteredUserController();
        $response = $controller->store($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('users', ['email' => 'mahasiswa@example.com']);
    }

    // ==========================================
    // METHOD 2: TEST LOGIN SAJA (Unit)
    // ==========================================
    #[Test]
    public function LoginBerhasil()
    {
        // Kita butuh factory karena ini test terpisah (DB bersih)
        $password = 'rahasia123';
        User::factory()->create([
            'email' => 'dosen@example.com',
            'password' => Hash::make($password),
        ]);

        $loginData = [
            'email' => 'dosen@example.com',
            'password' => $password,
            'recaptcha' => 'token_dummy',
        ];

        $request = Request::create('/api/login', 'POST', $loginData);
        $request->headers->set('Accept', 'application/json');

        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('verify')->andReturn(true);

        $controller = new LoginController();
        $response = $controller->login($request, $mockRecaptcha);

        $this->assertEquals(200, $response->getStatusCode());
        
        $result = $response->getData(true);
        $this->assertEquals('Login berhasil!', $result['message']);
        $this->assertArrayHasKey('token', $result);
    }

    // ==========================================
    // METHOD 3: TEST GABUNGAN (Integration via Function Call)
    // ==========================================
    #[Test]
    public function RegistrasiDanLangsungLoginBerhasil()
    {
        // --- STEP 1: REGISTRASI ---
        // Kita gunakan data yang sama untuk register dan login nanti
        $email = 'gabungan@example.com';
        $password = 'passwordKuat123';

        $regRequest = Request::create('/api/register', 'POST', [
            'name' => 'User Gabungan',
            'email' => $email,
            'password' => $password,
        ]);
        $regRequest->headers->set('Accept', 'application/json');

        Mail::fake();

        // Panggil Controller Register
        $regController = new RegisteredUserController();
        $regResponse = $regController->store($regRequest);

        // Pastikan Step 1 Sukses dulu
        $this->assertEquals(201, $regResponse->getStatusCode());

        // --- STEP 2: LOGIN ---
        // PENTING: Di sini kita TIDAK pakai User::factory().
        // Kenapa? Karena user 'gabungan@example.com' sudah tersimpan di database
        // berkat Step 1 di atas (dalam satu method, DB belum di-reset).

        $loginRequest = Request::create('/api/login', 'POST', [
            'email' => $email,     // Pakai email dari Step 1
            'password' => $password, // Pakai password dari Step 1
            'recaptcha' => 'token_valid',
        ]);
        $loginRequest->headers->set('Accept', 'application/json');

        // Mock Recaptcha
        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('verify')->andReturn(true);

        // Panggil Controller Login
        $loginController = new LoginController();
        $loginResponse = $loginController->login($loginRequest, $mockRecaptcha);

        // --- STEP 3: ASSERT FINAL ---
        $this->assertEquals(200, $loginResponse->getStatusCode());

        $result = $loginResponse->getData(true);
        
        // Buktikan login berhasil dan dapat token
        $this->assertEquals('Login berhasil!', $result['message']);
        $this->assertArrayHasKey('token', $result);
        
        // Buktikan yang login adalah user yang tadi register
        $this->assertEquals($email, $result['user']['email']);
    }
}