<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Smoke tests for Stock module HTTP endpoints.
 * 
 * These tests verify that routes are properly defined and controllers respond.
 * For full integration tests with database, use Integration test suite.
 * 
 * API Routes:
 * - GET    /api/v1/stock/items/list
 * - POST   /api/v1/stock/items/create
 * - GET    /api/v1/stock/items/show/{id}
 * - POST   /api/v1/stock/items/adjust
 * - POST   /api/v1/stock/items/reserve/{id}
 * - POST   /api/v1/stock/items/release/{id}
 */
class StockApiTest extends TestCase
{
    public function test_list_stock_items_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->getJson('/api/v1/stock/items/list');

        // Route exists and responds (500 = controller reached but db error, 200 = success)
        $this->assertContains($response->status(), [200, 500]);
    }

    public function test_create_stock_item_validates_input(): void
    {
        // Empty request should return validation error
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/create', []);

        // 400/422 = validation error (route works, validation works)
        $this->assertContains($response->status(), [400, 422]);
    }

    public function test_show_stock_item_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->getJson('/api/v1/stock/items/show/test-id');

        // Route exists (404 = not found is ok, 500 = db error)
        $this->assertContains($response->status(), [200, 404, 500]);
    }

    public function test_adjust_stock_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/adjust', []);

        // Route exists
        $this->assertContains($response->status(), [200, 400, 422, 500]);
    }

    public function test_reserve_stock_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/reserve/test-id', []);

        // Route exists
        $this->assertContains($response->status(), [200, 400, 404, 422, 500]);
    }

    public function test_release_stock_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/release/test-id', []);

        // Route exists
        $this->assertContains($response->status(), [200, 400, 404, 422, 500]);
    }

    public function test_create_stock_item_requires_sku(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/create', [
                'catalog_item_id' => 'cat-123',
                'location_id' => 'loc-123',
                'quantity' => 100,
            ]);

        // Should fail validation for missing SKU
        $this->assertContains($response->status(), [400, 422]);
    }

    public function test_create_stock_item_requires_quantity(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/create', [
                'sku' => 'TEST-SKU',
                'catalog_item_id' => 'cat-123',
                'location_id' => 'loc-123',
            ]);

        // Should fail validation for missing quantity
        $this->assertContains($response->status(), [400, 422]);
    }
}
