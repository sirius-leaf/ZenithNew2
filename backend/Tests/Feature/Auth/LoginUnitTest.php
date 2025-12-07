<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Tests\Feature\Auth\Modul\Loginmodul; // <--- Pakai Replika
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class LoginUnitTest extends TestCase
{
    use RefreshDatabase;

    // 1. TEST SUKSES LOGIN
    #[Test]
    public function login_sukses_menggunakan_replika()
    {
        // Arrange
        $password = 'rahasia123';
        User::factory()->create([
            'email' => 'dosen@example.com',
            'password' => Hash::make($password),
        ]);

        $loginData = [
            'email' => 'dosen@example.com',
            'password' => $password,
            // Recaptcha TIDAK PERLU lagi
        ];

        $request = Request::create('/api/login', 'POST', $loginData);
        $request->headers->set('Accept', 'application/json');

        // Act (Function Call)
        // Tidak perlu inject mock service lagi, cukup $request
        $modul = new Loginmodul();
        $response = $modul->login($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());

        $result = $response->getData(true);
        $this->assertEquals('Login berhasil!', $result['message']);
        $this->assertArrayHasKey('token', $result);
    }

    // 2. TEST GAGAL PASSWORD SALAH
    #[Test]
    public function login_gagal_password_salah()
    {
        // Arrange
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password_benar'),
        ]);

        $request = Request::create('/api/login', 'POST', [
            'email' => 'user@example.com',
            'password' => 'password_salah',
        ]);
        $request->headers->set('Accept', 'application/json');

        // Act
        $modul = new Loginmodul();
        $response = $modul->login($request);

        // Assert (Harusnya 401)
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Email atau password salah.', $response->getData()->message);
    }

    // 3. TEST GAGAL USER BANNED
    #[Test]
    public function login_gagal_karena_banned()
    {
        // Arrange: User dengan status banned
        User::factory()->create([
            'email' => 'banned@example.com',
            'password' => Hash::make('pass'),
            'is_banned' => true,
        ]);

        $request = Request::create('/api/login', 'POST', [
            'email' => 'banned@example.com',
            'password' => 'pass',
        ]);
        $request->headers->set('Accept', 'application/json');

        // Act
        $modul = new Loginmodul();
        $response = $modul->login($request);

        // Assert (Harusnya 403 Forbidden)
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals(true, $response->getData()->banned);
    }

    // 4. TEST LOGOUT
    #[Test]
    public function logout_berhasil()
    {
        // Arrange
        $user = User::factory()->create();

        // Buat token manual dan tempelkan ke user
        $tokenObj = $user->createToken('test');
        $user->withAccessToken($tokenObj->accessToken);

        // Request Logout
        $request = Request::create('/api/logout', 'POST');
        $request->headers->set('Accept', 'application/json');

        // Set user ke request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Act
        $modul = new Loginmodul();
        $response = $modul->logout($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());

        // Pastikan token hilang dari DB
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id
        ]);
    }
}
