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
    // METHOD 1: TEST REGISTRASI SAJA
    // ==========================================
    #[Test]
    public function RegistrasiBerhasil()
    {
        // 1. Siapkan Data Registrasi
        $data = [
            'name' => 'Mahasiswa Baru',
            'email' => 'mahasiswa@example.com',
            'password' => 'password123',
        ];

        // 2. Buat Request Manual
        $request = Request::create('/api/register', 'POST', $data);
        $request->headers->set('Accept', 'application/json');

        // 3. Mock Mailer
        Mail::fake();

        // 4. Panggil Controller Register (Function Call)
        $controller = new RegisteredUserController();
        $response = $controller->store($request);

        // 5. Assert (Pastikan Sukses)
        $this->assertEquals(201, $response->getStatusCode());
        
        // Pastikan data masuk ke DB
        $this->assertDatabaseHas('users', [
            'email' => 'mahasiswa@example.com'
        ]);
    }

    // ==========================================
    // METHOD 2: TEST LOGIN SAJA
    // ==========================================
    #[Test]
    public function LoginBerhasil()
    {
        // 1. PERSIAPAN DATA (PENTING)
        // Karena database di-reset setelah test di atas selesai,
        // Kita WAJIB membuat user baru lagi di sini agar bisa Login.
        $password = 'rahasia123';
        $user = User::factory()->create([
            'email' => 'dosen@example.com',
            'password' => Hash::make($password), // Password di DB harus ter-hash
        ]);

        // 2. Siapkan Request Login (Kirim password asli/plain)
        $loginData = [
            'email' => 'dosen@example.com',
            'password' => $password,
            'recaptcha' => 'token_dummy',
        ];

        $request = Request::create('/api/login', 'POST', $loginData);
        $request->headers->set('Accept', 'application/json');

        // 3. Mock Service Recaptcha
        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('verify')->andReturn(true);

        // 4. Panggil Controller Login (Function Call)
        $controller = new LoginController();
        $response = $controller->login($request, $mockRecaptcha);

        // 5. Assert (Pastikan Sukses)
        $this->assertEquals(200, $response->getStatusCode());
        
        // Cek isi respon
        $result = $response->getData(true);
        $this->assertEquals('Login berhasil!', $result['message']);
        $this->assertArrayHasKey('token', $result);
    }
}