<?php

namespace App\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class VesselAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Define a test route protected by the middleware
        Route::get('/api/v1/test-protected', function () {
            return response()->json([
                'message' => 'Access Granted',
                'workspace_id' => request()->attributes->get('workspace_id'),
                'scope' => request()->attributes->get('token_scope')
            ]);
        })->middleware('vessel.access:all');

        Route::get('/api/v1/test-protected-own', function () {
            return response()->json([
                'message' => 'Access Granted',
                'workspace_id' => request()->attributes->get('workspace_id'),
                'scope' => request()->attributes->get('token_scope')
            ]);
        })->middleware('vessel.access:own');
    }

    public function test_it_denies_access_without_header()
    {
        $response = $this->getJson('/api/v1/test-protected');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized', 'message' => 'Missing VESSEL-ACCESS-PRIVATE header']);
    }

    public function test_it_denies_access_with_invalid_token()
    {
        $response = $this->withHeaders([
            'VESSEL-ACCESS-PRIVATE' => 'invalid-token'
        ])->getJson('/api/v1/test-protected');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized', 'message' => 'Invalid token']);
    }

    public function test_it_grants_access_with_valid_token_and_all_scope()
    {
        // Create a token
        DB::table('auth_access_tokens')->insert([
            'id' => Str::uuid()->toString(),
            'token' => 'valid-token-all',
            'workspace_id' => 123,
            'scope' => 'all',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders([
            'VESSEL-ACCESS-PRIVATE' => 'valid-token-all'
        ])->getJson('/api/v1/test-protected');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Access Granted',
                'workspace_id' => 123,
                'scope' => 'all'
            ]);
    }

    public function test_it_grants_access_with_valid_token_and_own_scope_to_own_route()
    {
        // Create a token
        DB::table('auth_access_tokens')->insert([
            'id' => Str::uuid()->toString(),
            'token' => 'valid-token-own',
            'workspace_id' => 456,
            'scope' => 'own',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders([
            'VESSEL-ACCESS-PRIVATE' => 'valid-token-own'
        ])->getJson('/api/v1/test-protected-own');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Access Granted',
                'workspace_id' => 456,
                'scope' => 'own'
            ]);
    }

    public function test_it_denies_access_with_own_scope_to_all_route()
    {
        // Create a token with 'own' scope
        DB::table('auth_access_tokens')->insert([
            'id' => Str::uuid()->toString(),
            'token' => 'valid-token-own',
            'workspace_id' => 789,
            'scope' => 'own',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Try to access a route requiring 'all'
        $response = $this->withHeaders([
            'VESSEL-ACCESS-PRIVATE' => 'valid-token-own'
        ])->getJson('/api/v1/test-protected');

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden', 'message' => 'Insufficient scope']);
    }
}
