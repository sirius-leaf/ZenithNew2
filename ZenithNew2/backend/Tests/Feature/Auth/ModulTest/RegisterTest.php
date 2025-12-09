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

class RegisterTest extends TestCase
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
}
