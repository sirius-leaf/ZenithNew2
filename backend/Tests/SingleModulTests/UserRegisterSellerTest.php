<?php

namespace Tests\SingleModulTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use CodeTests\SingleModul\UserRegisterSeller;

require_once __DIR__ . '/../SingleModul/UserRegisterSeller.php';
require_once __DIR__ . '/../TestCase.php';

class UserRegisterSellerTest extends TestCase
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

        // Mock validate method or use a real request object that handles validation if possible.
        // Since the replica code calls $request->validate(), we need to ensure the request object supports it.
        // In a real controller test, we usually call the route. 
        // But here we are testing the class method directly.
        // $request->validate() might fail if not bound to container correctly or if using Illuminate\Http\Request directly.
        // However, let's try to simulate the controller call or just mock the validation if needed.
        // Actually, $request->validate() is a macro or method on Request. 
        // To make it easier, we can swap the request instance in the container or just rely on Laravel's Request facade/object.

        // Better approach for unit testing this specific class without a route:
        // We can't easily mock $request->validate() on a standard Request object without some setup.
        // But since we are in Laravel TestCase, we can try to use a FormRequest or just pass valid data.
        // Wait, $request->validate() throws ValidationException.

        // Let's try to just call the method.
        // We need to set the user resolver for the request if it uses $request->user().
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $controller = new UserRegisterSeller();
        $response = $controller->requestSeller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('penjual_pending', $user->fresh()->role);
        Storage::disk('public')->assertExists('documents/ktp/' . $file->hashName());
    }
}
