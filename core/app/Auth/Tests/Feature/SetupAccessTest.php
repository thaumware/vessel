<?php

namespace App\Auth\Tests\Feature;

use Tests\TestCase;

class SetupAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        putenv('APP_INSTALLED=true');
        config([
            'app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
        ]);
    }

    public function test_installed_app_redirects_setup_without_admin_session(): void
    {
        $response = $this->get('/setup');

        $response->assertRedirect('/admin');
    }

    public function test_installed_app_allows_setup_with_admin_session(): void
    {
        $response = $this->withSession([
            'admin_authenticated' => true,
        ])->get('/setup');

        $response->assertOk();
        $response->assertSee('Vessel - Configuraci', false);
    }

    public function test_installed_app_rejects_setup_post_without_admin_session(): void
    {
        $response = $this->postJson('/setup', [
            'db_driver' => 'sqlite',
            'db_path' => database_path('database.sqlite'),
            'app_url' => 'http://localhost',
            'admin_user' => 'admin',
            'admin_pass' => 'secret',
            'fresh' => false,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
