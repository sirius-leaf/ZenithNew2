<?php

namespace Tests\SingleModulTests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Toko;
use CodeTests\SingleModul\AdminConfirmSeller;

require_once __DIR__ . '/../SingleModul/AdminConfirmSeller.php';
require_once __DIR__ . '/../TestCase.php';

class AdminConfirmSellerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_seller_request()
    {
        $user = User::factory()->create([
            'role' => 'penjual_pending',
            'store_name' => 'Pending Store',
            'description' => 'Pending Description',
        ]);

        $controller = new AdminConfirmSeller();
        $response = $controller->approve($user->id);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('penjual', $user->fresh()->role);
        $this->assertDatabaseHas('tokos', [
            'toko_name' => 'Pending Store',
            'deskripsi' => 'Pending Description',
            'id_user' => $user->id, // Assuming id_user FK exists
        ]);
    }

    public function test_cannot_approve_non_pending_user()
    {
        $user = User::factory()->create(['role' => 'user']);

        $controller = new AdminConfirmSeller();
        $response = $controller->approve($user->id);

        $this->assertEquals(400, $response->getStatusCode());
    }
}
