<?php

declare(strict_types=1);

namespace App\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTokensTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Minimal env so AdminPanelAuth allows default admin credentials.
        putenv('APP_INSTALLED=true');
        putenv('ADMIN_ROOT=admin');
        putenv('ADMIN_ROOT_PASSWORD=admin123');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
    }

    public function test_admin_can_create_list_and_revoke_tokens(): void
    {
        // CSRF middleware is not relevant for JSON Feature tests here.
        // Disable global middleware stack to simplify web guard and CSRF during the test.
        $this->withoutMiddleware();

        // Create token
        $create = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin:admin123'),
        ])->postJson('/admin/tokens', [
            'name' => 'Test Token',
            'scope' => 'own',
            'workspace_id' => 'workspace-x',
        ]);

        $create->assertStatus(200)->assertJson([
            'success' => true,
            'token' => [
                'name' => 'Test Token',
                'scope' => 'own',
                'workspace_id' => 'workspace-x',
            ],
        ]);

        $tokenId = $create->json('token.id');
        $tokenValue = $create->json('token.token');

        $this->assertNotEmpty($tokenId);
        $this->assertNotEmpty($tokenValue);
        $this->assertDatabaseHas('auth_access_tokens', [
            'id' => $tokenId,
            'scope' => 'own',
            'workspace_id' => 'workspace-x',
        ]);

        // List tokens
        $list = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin:admin123'),
        ])->getJson('/admin/tokens');

        $list->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['id' => $tokenId]);

        // Revoke token
        $delete = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('admin:admin123'),
        ])->deleteJson('/admin/tokens/' . $tokenId);

        $delete->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('auth_access_tokens', ['id' => $tokenId]);
    }
}
