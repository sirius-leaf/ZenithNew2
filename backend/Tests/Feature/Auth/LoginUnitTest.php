<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Http\Controllers\Auth\LoginController;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class LoginUnitTest extends TestCase
{
    use RefreshDatabase;

    // 1. TEST SUKSES LOGIN
    #[Test]
    public function test_login_success()
    {
        $password = 'rahasia123';
        $user = User::factory()->create([
            'email' => 'dosen@example.com',
            'password' => Hash::make($password),
            'is_banned' => false
        ]);

        $request = Request::create('/api/login', 'POST', [
            'email' => 'dosen@example.com',
            'password' => $password,
            'recaptcha' => 'valid_token',
        ]);
        $request->headers->set('Accept', 'application/json');

        // Mock Recaptcha Sukses
        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('verify')->andReturn(true);

        $controller = new LoginController();
        $response = $controller->login($request, $mockRecaptcha);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Login berhasil!', $response->getData()->message);
    }

    // 2. TEST GAGAL PASSWORD SALAH
    #[Test]
    public function test_login_fails_wrong_password()
    {
        // User ada, password 'benar'
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password_benar'),
        ]);

        // Request kirim password 'salah'
        $request = Request::create('/api/login', 'POST', [
            'email' => 'user@example.com',
            'password' => 'password_salah',
            'recaptcha' => 'valid_token',
        ]);
        $request->headers->set('Accept', 'application/json');

        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('verify')->andReturn(true);

        $controller = new LoginController();
        $response = $controller->login($request, $mockRecaptcha);

        // Harapannya 401 Unauthorized
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Email atau password salah.', $response->getData()->message);
    }

    // 3. TEST GAGAL RECAPTCHA
    #[Test]
    public function test_login_fails_invalid_recaptcha()
    {
        $request = Request::create('/api/login', 'POST', [
            'email' => 'random@example.com',
            'password' => 'any',
            'recaptcha' => 'invalid_token',
        ]);
        $request->headers->set('Accept', 'application/json');

        // Mock Recaptcha return FALSE (Gagal)
        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('verify')->andReturn(false);

        $controller = new LoginController();
        $response = $controller->login($request, $mockRecaptcha);

        // Harapannya 422 Unprocessable Entity
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Recaptcha verification failed.', $response->getData()->message);
    }

    // 4. TEST GAGAL USER BANNED
    #[Test]
    public function test_login_fails_banned_user()
    {
        $password = 'password123';
        User::factory()->create([
            'email' => 'banned@example.com',
            'password' => Hash::make($password),
            'is_banned' => true, // Status Banned
        ]);

        $request = Request::create('/api/login', 'POST', [
            'email' => 'banned@example.com',
            'password' => $password,
            'recaptcha' => 'valid_token',
        ]);
        $request->headers->set('Accept', 'application/json');

        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('verify')->andReturn(true);

        $controller = new LoginController();
        $response = $controller->login($request, $mockRecaptcha);

        // Harapannya 403 Forbidden
        $this->assertEquals(403, $response->getStatusCode());
        // Menggunakan assertStringContainsString karena pesannya panjang
        $this->assertStringContainsString('akun anda dibatasi', $response->getData()->message);
    }

    // 5. TEST LOGOUT
    #[Test]
    public function test_logout_success()
    {
        // 1. Buat User
        $user = User::factory()->create();
        
        // 2. Buat Token, tapi simpan object lengkapnya (bukan cuma string plainTextToken)
        $tokenResult = $user->createToken('test'); 
        
        // 3. PENTING: Set token tersebut sebagai 'currentAccessToken' secara manual
        // Agar function currentAccessToken() di controller nanti tidak return null
        $user->withAccessToken($tokenResult->accessToken);

        // 4. Siapkan Request
        $request = Request::create('/api/logout', 'POST');
        $request->headers->set('Accept', 'application/json');
        
        // 5. Masukkan user yang SUDAH PUNYA access token ke dalam request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // 6. Panggil Controller
        $controller = new LoginController();
        $response = $controller->logout($request);

        // 7. Assert
        $this->assertEquals(200, $response->getStatusCode());
        
        // Pastikan token benar-benar terhapus dari database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenResult->accessToken->id
        ]);
    }
}