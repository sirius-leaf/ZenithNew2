<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException; // Wajib import ini untuk cek error
use PHPUnit\Framework\Attributes\Test;

class RegistrationUnitTest extends TestCase
{
    use RefreshDatabase;

    // 1. TEST SUKSES (Yang tadi sudah dibuat)
    #[Test]
    public function test_register_controller_success()
    {
        $data = [
            'name' => 'Mahasiswa',
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

    // 2. TEST GAGAL VALIDASI (Input Kosong)
    #[Test]
    public function test_register_controller_fails_validation_empty_data()
    {
        // Siapkan data kosong
        $request = Request::create('/api/register', 'POST', []);
        $request->headers->set('Accept', 'application/json');

        $controller = new RegisteredUserController();

        // KITA HARAPKAN ERROR:
        // Karena ini function call langsung, dia tidak return 422,
        // tapi melempar 'ValidationException'.
        $this->expectException(ValidationException::class);

        // Panggil fungsi (ini akan menyebabkan crash/exception yang ditangkap di atas)
        $controller->store($request);
    }

    // 3. TEST GAGAL DUPLIKAT (Email Kembar)
    #[Test]
    public function test_register_controller_fails_duplicate_email()
    {
        // Buat user pertama dulu
        User::factory()->create(['email' => 'kembar@example.com']);

        // Coba register lagi dengan email sama
        $data = [
            'name' => 'Orang Kedua',
            'email' => 'kembar@example.com', // SAMA
            'password' => 'password123',
        ];

        $request = Request::create('/api/register', 'POST', $data);
        $request->headers->set('Accept', 'application/json');

        $controller = new RegisteredUserController();

        // Harapannya error validasi lagi (karena rule 'unique:users')
        $this->expectException(ValidationException::class);

        $controller->store($request);
    }
}