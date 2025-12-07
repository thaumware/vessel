<?php

namespace Tests\Feature;

use App\Auth\Infrastructure\In\Http\Middleware\AdminPanelAuth;
use App\Shared\Infrastructure\ConfigStore;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminPanelAuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure we don't get redirected to setup during tests
        putenv('APP_INSTALLED=true');
        putenv('ADMIN_ROOT=admin');
        putenv('ADMIN_ROOT_PASSWORD=admin123');
        // Use sqlite memory to satisfy schema checks in ConfigStore
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
    }

    public function test_denies_access_without_credentials(): void
    {
        app()->instance(ConfigStore::class, new class {
            public function get(string $key, $default = null)
            {
                return null;
            }
        });

        $middleware = new AdminPanelAuth();
        $request = Request::create('/admin', 'GET');

        $response = $middleware->handle($request, fn () => response('OK'));

        // Should return 401 and show login form (not WWW-Authenticate header)
        $this->assertSame(401, $response->getStatusCode());
        $this->assertStringContainsString('login', $response->getContent());
    }

    public function test_allows_access_with_default_credentials(): void
    {
        app()->instance(ConfigStore::class, new class {
            public function get(string $key, $default = null)
            {
                return match ($key) {
                    'admin.root' => 'admin',
                    'admin.root_password' => 'admin123',
                    default => null,
                };
            }
        });

        $middleware = new AdminPanelAuth();
        $request = Request::create('/admin', 'GET');
        $request->headers->set('Authorization', 'Basic ' . base64_encode('admin:admin123'));
        $request->server->set('PHP_AUTH_USER', 'admin');
        $request->server->set('PHP_AUTH_PW', 'admin123');
        $request->attributes->set('basic_user', 'admin');
        $request->attributes->set('basic_pass', 'admin123');

        $response = $middleware->handle($request, fn () => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }
}
