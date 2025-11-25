<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Feature tests for Stock module HTTP endpoints.
 */
class StockApiTest extends TestCase
{
    public function test_can_list_stock_items_with_local_adapter(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->getJson('/api/stock/items');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'sku',
                    'catalog_item_id',
                    'catalog_origin',
                    'location_id',
                    'quantity',
                    'reserved_quantity',
                    'available_quantity',
                ]
            ]
        ]);
    }

    public function test_can_get_single_stock_item(): void
    {
        // First list to get an ID
        $listResponse = $this->withAdapter('stock', 'local')
            ->getJson('/api/stock/items');

        if ($listResponse->status() !== 200 || empty($listResponse->json('data'))) {
            $this->markTestSkipped('No stock items available for testing');
        }

        $itemId = $listResponse->json('data.0.id');

        $response = $this->withAdapter('stock', 'local')
            ->getJson("/api/stock/items/{$itemId}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $itemId);
    }

    public function test_can_create_stock_item(): void
    {
        $data = [
            'sku' => 'TEST-CREATE-' . time(),
            'catalog_item_id' => 'cat-' . uniqid(),
            'catalog_origin' => 'internal_catalog',
            'location_id' => 'loc-' . uniqid(),
            'location_type' => 'warehouse',
            'quantity' => 100,
        ];

        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/stock/items', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.sku', $data['sku']);
        $response->assertJsonPath('data.quantity', 100);
    }

    public function test_can_adjust_stock_quantity(): void
    {
        // Create item first
        $createData = [
            'sku' => 'TEST-ADJUST-' . time(),
            'catalog_item_id' => 'cat-' . uniqid(),
            'location_id' => 'loc-' . uniqid(),
            'quantity' => 50,
        ];

        $createResponse = $this->withAdapter('stock', 'local')
            ->postJson('/api/stock/items', $createData);

        if ($createResponse->status() !== 201) {
            $this->markTestSkipped('Could not create stock item for adjustment test');
        }

        $sku = $createResponse->json('data.sku');
        $locationId = $createResponse->json('data.location_id');

        // Adjust quantity
        $adjustResponse = $this->withAdapter('stock', 'local')
            ->postJson('/api/stock/items/adjust', [
                'sku' => $sku,
                'location_id' => $locationId,
                'delta' => 25,
            ]);

        $adjustResponse->assertStatus(200);
        $adjustResponse->assertJsonPath('data.quantity', 75);
    }

    public function test_can_reserve_stock(): void
    {
        // Create item first
        $createData = [
            'sku' => 'TEST-RESERVE-' . time(),
            'catalog_item_id' => 'cat-' . uniqid(),
            'location_id' => 'loc-' . uniqid(),
            'quantity' => 100,
            'reserved_quantity' => 0,
        ];

        $createResponse = $this->withAdapter('stock', 'local')
            ->postJson('/api/stock/items', $createData);

        if ($createResponse->status() !== 201) {
            $this->markTestSkipped('Could not create stock item for reserve test');
        }

        $itemId = $createResponse->json('data.id');

        // Reserve stock
        $reserveResponse = $this->withAdapter('stock', 'local')
            ->postJson("/api/stock/items/{$itemId}/reserve", [
                'quantity' => 30,
            ]);

        $reserveResponse->assertStatus(200);
        $reserveResponse->assertJsonPath('data.reserved_quantity', 30);
        $reserveResponse->assertJsonPath('data.available_quantity', 70);
    }

    public function test_reserve_fails_when_insufficient_stock(): void
    {
        // Create item with low stock
        $createData = [
            'sku' => 'TEST-RESERVE-FAIL-' . time(),
            'catalog_item_id' => 'cat-' . uniqid(),
            'location_id' => 'loc-' . uniqid(),
            'quantity' => 10,
            'reserved_quantity' => 5,
        ];

        $createResponse = $this->withAdapter('stock', 'local')
            ->postJson('/api/stock/items', $createData);

        if ($createResponse->status() !== 201) {
            $this->markTestSkipped('Could not create stock item');
        }

        $itemId = $createResponse->json('data.id');

        // Try to reserve more than available
        $reserveResponse = $this->withAdapter('stock', 'local')
            ->postJson("/api/stock/items/{$itemId}/reserve", [
                'quantity' => 10, // Only 5 available
            ]);

        $reserveResponse->assertStatus(422);
    }

    public function test_can_release_reserved_stock(): void
    {
        // Create item with reserved stock
        $createData = [
            'sku' => 'TEST-RELEASE-' . time(),
            'catalog_item_id' => 'cat-' . uniqid(),
            'location_id' => 'loc-' . uniqid(),
            'quantity' => 100,
            'reserved_quantity' => 50,
        ];

        $createResponse = $this->withAdapter('stock', 'local')
            ->postJson('/api/stock/items', $createData);

        if ($createResponse->status() !== 201) {
            $this->markTestSkipped('Could not create stock item');
        }

        $itemId = $createResponse->json('data.id');

        // Release stock
        $releaseResponse = $this->withAdapter('stock', 'local')
            ->postJson("/api/stock/items/{$itemId}/release", [
                'quantity' => 20,
            ]);

        $releaseResponse->assertStatus(200);
        $releaseResponse->assertJsonPath('data.reserved_quantity', 30);
        $releaseResponse->assertJsonPath('data.available_quantity', 70);
    }
}
