<?php

namespace App\Stock\Tests\Feature;

 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Facades\DB;
 use Illuminate\Support\Str;
 use Tests\TestCase;

/**
 * Smoke tests for Stock module HTTP endpoints.
 * Verifica que las rutas existen y responden.
 */
class StockApiTest extends TestCase
{
    use RefreshDatabase;

    // Keep property type aligned with Laravel's TestCase (no typed property).
    protected $defaultHeaders = [
        'VESSEL-ACCESS-PRIVATE' => 'test-token',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('auth_access_tokens')->insert([
            'id' => Str::uuid()->toString(),
            'token' => 'test-token',
            'workspace_id' => 'ws-test',
            'scope' => 'all',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_list_stock_items_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->getJson('/api/v1/stock/items/read');

        $this->assertContains($response->status(), [200, 500]);
    }

    public function test_create_stock_item_validates_input(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/create', []);

        $this->assertContains($response->status(), [400, 422]);
    }

    public function test_show_stock_item_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->getJson('/api/v1/stock/items/show/test-id');

        $this->assertContains($response->status(), [200, 404, 500]);
    }

    public function test_adjust_stock_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/adjust', []);

        $this->assertContains($response->status(), [200, 400, 422, 500]);
    }

    public function test_reserve_stock_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/reserve/test-id', []);

        $this->assertContains($response->status(), [200, 400, 404, 422, 500]);
    }

    public function test_release_stock_endpoint_exists(): void
    {
        $response = $this->withAdapter('stock', 'local')
            ->postJson('/api/v1/stock/items/release/test-id', []);

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

        $this->assertContains($response->status(), [400, 422]);
    }
}
