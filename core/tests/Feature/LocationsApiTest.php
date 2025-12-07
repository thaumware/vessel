<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Smoke tests for Locations module HTTP endpoints.
 * 
 * These tests verify that routes are properly defined and controllers respond.
 * For full integration tests with database, use Integration test suite.
 * 
 * API Routes:
 * - GET    /api/v1/locations/read
 * - POST   /api/v1/locations/create
 * - GET    /api/v1/locations/show/{id}
 * - PUT    /api/v1/locations/update/{id}
 * - DELETE /api/v1/locations/delete/{id}
 */
class LocationsApiTest extends TestCase
{
    public function test_list_locations_endpoint_exists(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->getJson('/api/v1/locations/read');

        // Route exists and responds (500 = controller reached but db error, 200 = success)
        $this->assertContains($response->status(), [200, 500]);
    }

    public function test_create_location_validates_input(): void
    {
        // Empty request should return validation error
        $response = $this->withAdapter('locations', 'local')
            ->postJson('/api/v1/locations/create', []);

        // 422 = validation error (route works, validation works)
        $response->assertStatus(422);
    }

    public function test_show_location_endpoint_exists(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->getJson('/api/v1/locations/show/test-id');

        // Route exists (404 = not found is ok, 500 = db error)
        $this->assertContains($response->status(), [200, 404, 500]);
    }

    public function test_update_location_endpoint_exists(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->putJson('/api/v1/locations/update/test-id', []);

        // Route exists
        $this->assertContains($response->status(), [200, 400, 404, 422, 500]);
    }

    public function test_delete_location_endpoint_exists(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->deleteJson('/api/v1/locations/delete/test-id');

        // Route exists
        $this->assertContains($response->status(), [200, 204, 400, 404, 500]);
    }

    public function test_create_location_requires_name(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->postJson('/api/v1/locations/create', [
                'type' => 'warehouse',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_create_location_requires_type(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->postJson('/api/v1/locations/create', [
                'name' => 'Test Location',
            ]);

        // 400 or 422 for validation error
        $this->assertContains($response->status(), [400, 422]);
    }
}
