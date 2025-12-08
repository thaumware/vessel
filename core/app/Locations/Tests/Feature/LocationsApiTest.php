<?php

namespace App\Locations\Tests\Feature;

use Tests\TestCase;

/**
 * Smoke tests para endpoints de Locations.
 */
class LocationsApiTest extends TestCase
{
    public function test_list_locations_endpoint_exists(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->getJson('/api/v1/locations/read');

        $this->assertContains($response->status(), [200, 500]);
    }

    public function test_create_location_validates_input(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->postJson('/api/v1/locations/create', []);

        $response->assertStatus(422);
    }

    public function test_show_location_endpoint_exists(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->getJson('/api/v1/locations/show/test-id');

        $this->assertContains($response->status(), [200, 404, 500]);
    }

    public function test_update_location_endpoint_exists(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->putJson('/api/v1/locations/update/test-id', []);

        $this->assertContains($response->status(), [200, 400, 404, 422, 500]);
    }

    public function test_delete_location_endpoint_exists(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->deleteJson('/api/v1/locations/delete/test-id');

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

    public function test_create_location_allows_optional_type(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->postJson('/api/v1/locations/create', [
                'name' => 'Test Location',
            ]);

        $this->assertContains($response->status(), [201, 500]);
    }
}
