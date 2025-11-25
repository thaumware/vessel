<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Feature tests for Locations module HTTP endpoints.
 */
class LocationsApiTest extends TestCase
{
    public function test_can_list_locations_with_local_adapter(): void
    {
        $response = $this->withAdapter('locations', 'local')
            ->getJson('/api/locations/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'type',
                ]
            ]
        ]);
    }

    public function test_can_get_single_location(): void
    {
        // First list to get an ID
        $listResponse = $this->withAdapter('locations', 'local')
            ->getJson('/api/locations/list');

        if (empty($listResponse->json('data'))) {
            $this->markTestSkipped('No locations available for testing');
        }

        $locationId = $listResponse->json('data.0.id');

        $response = $this->withAdapter('locations', 'local')
            ->getJson("/api/locations/show/{$locationId}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $locationId);
    }

    public function test_can_create_location(): void
    {
        $data = [
            'name' => 'Test Warehouse ' . time(),
            'address_id' => 'addr-' . uniqid(),
            'type' => 'warehouse',
            'description' => 'A test warehouse',
        ];

        $response = $this->withAdapter('locations', 'local')
            ->postJson('/api/locations/create', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', $data['name']);
        $response->assertJsonPath('data.type', 'warehouse');
    }

    public function test_can_update_location(): void
    {
        // Create first
        $createResponse = $this->withAdapter('locations', 'local')
            ->postJson('/api/locations/create', [
                'name' => 'Original Name ' . time(),
                'address_id' => 'addr-' . uniqid(),
                'type' => 'warehouse',
            ]);

        if ($createResponse->status() !== 201) {
            $this->markTestSkipped('Could not create location');
        }

        $locationId = $createResponse->json('data.id');

        // Update
        $updateResponse = $this->withAdapter('locations', 'local')
            ->putJson("/api/locations/update/{$locationId}", [
                'name' => 'Updated Name',
            ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_can_delete_location(): void
    {
        // Create first
        $createResponse = $this->withAdapter('locations', 'local')
            ->postJson('/api/locations/create', [
                'name' => 'To Delete ' . time(),
                'address_id' => 'addr-' . uniqid(),
                'type' => 'warehouse',
            ]);

        if ($createResponse->status() !== 201) {
            $this->markTestSkipped('Could not create location');
        }

        $locationId = $createResponse->json('data.id');

        // Delete
        $deleteResponse = $this->withAdapter('locations', 'local')
            ->deleteJson("/api/locations/delete/{$locationId}");

        $deleteResponse->assertStatus(200);

        // Verify deleted
        $getResponse = $this->withAdapter('locations', 'local')
            ->getJson("/api/locations/show/{$locationId}");

        $getResponse->assertStatus(404);
    }
}
