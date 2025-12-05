<?php

namespace Tests\GroupedModulTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use CodeTests\GroupedModul\SellerGroup;

require_once __DIR__ . '/../GroupedModul/SellerGroup.php';
require_once __DIR__ . '/../TestCase.php';

class SellerGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_to_become_seller()
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);
        Auth::login($user);

        $request = new \Illuminate\Http\Request();
        $request->merge([
            'store_name' => 'My Store',
            'address' => '123 Street',
            'description' => 'Best store',
        ]);

        $file = UploadedFile::fake()->create('ktp.jpg');
        $file2 = UploadedFile::fake()->create('npwp.jpg');

        $request->files->set('ktp', $file);
        $request->files->set('npwp', $file2);

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $controller = new SellerGroup();
        $response = $controller->requestSeller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('penjual_pending', $user->fresh()->role);
        Storage::disk('public')->assertExists('documents/ktp/' . $file->hashName());
    }

    public function test_admin_can_approve_seller_request()
    {
        $user = User::factory()->create([
            'role' => 'penjual_pending',
            'store_name' => 'Pending Store',
            'description' => 'Pending Description',
        ]);

        $controller = new SellerGroup();
        $response = $controller->approve($user->id);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('penjual', $user->fresh()->role);
        $this->assertDatabaseHas('tokos', [
            'toko_name' => 'Pending Store',
            'deskripsi' => 'Pending Description',
            'id_user' => $user->id,
        ]);
    }

    public function test_cannot_approve_non_pending_user()
    {
        $user = User::factory()->create(['role' => 'user']);

        $controller = new SellerGroup();
        $response = $controller->approve($user->id);

        $this->assertEquals(400, $response->getStatusCode());
    }
}
