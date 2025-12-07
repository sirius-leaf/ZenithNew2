<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Tests\Feature\Auth\Modul\Registermodul; // <--- Pakai Replika
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class RegisterUnitTest extends TestCase
{
    use RefreshDatabase;

    // 1. TEST SUKSES
    #[Test]
    public function registrasi_sukses_menggunakan_replika()
    {
        // Arrange
        $data = [
            'name' => 'Mahasiswa Rajin',
            'email' => 'rajin@example.com',
            'password' => 'password123',
        ];

        $request = Request::create('/api/register', 'POST', $data);
        $request->headers->set('Accept', 'application/json');

        // Act (Function Call)
        $modul = new Registermodul();
        $response = $modul->store($request);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('users', ['email' => 'rajin@example.com']);
    }

    // 2. TEST GAGAL (Data Kosong)
    #[Test]
    public function registrasi_gagal_karena_data_kosong()
    {
        // Arrange (Data kosong)
        $request = Request::create('/api/register', 'POST', []);
        $request->headers->set('Accept', 'application/json');

        $modul = new Registermodul();

        // Expect Exception (Karena function call melempar error validasi mentah)
        $this->expectException(ValidationException::class);

        // Act
        $modul->store($request);
    }

    // 3. TEST GAGAL (Email Duplikat)
    #[Test]
    public function registrasi_gagal_karena_email_sudah_ada()
    {
        // Arrange: Buat user duluan
        User::factory()->create(['email' => 'kembar@example.com']);

        // Coba daftar pakai email sama
        $data = [
            'name' => 'Orang Kedua',
            'email' => 'kembar@example.com',
            'password' => 'password123',
        ];

        $request = Request::create('/api/register', 'POST', $data);
        $request->headers->set('Accept', 'application/json');

        $modul = new Registermodul();

        // Expect Exception
        $this->expectException(ValidationException::class);

        // Act
        $modul->store($request);
    }
}
