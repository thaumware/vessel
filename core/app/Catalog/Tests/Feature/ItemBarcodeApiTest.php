<?php

namespace App\Catalog\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemBarcodeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => 'app/Catalog/Infrastructure/Migrations', '--realpath' => false]);
    }

    public function test_can_create_and_find_a_product_by_barcode(): void
    {
        $itemResponse = $this->postJson('/api/v1/items/create', [
            'name' => 'Yogur Natural 500 g',
            'status' => 'active',
        ]);

        $itemResponse->assertCreated();
        $itemId = $itemResponse->json('data.id');

        $identifierResponse = $this->postJson("/api/v1/items/{$itemId}/identifiers/create", [
            'type' => 'ean',
            'value' => '7791234567890',
            'is_primary' => true,
        ]);

        $identifierResponse->assertCreated();
        $identifierResponse->assertJsonPath('data.type', 'ean');
        $identifierResponse->assertJsonPath('data.value', '7791234567890');

        $lookupResponse = $this->getJson('/api/v1/items/by-barcode/7791234567890');

        $lookupResponse->assertOk();
        $lookupResponse->assertJsonPath('data.id', $itemId);
        $lookupResponse->assertJsonPath('data.name', 'Yogur Natural 500 g');
        $lookupResponse->assertJsonPath('data.identifier.type', 'ean');
        $lookupResponse->assertJsonPath('data.identifier.value', '7791234567890');
    }

    public function test_returns_not_found_for_an_unknown_barcode(): void
    {
        $response = $this->getJson('/api/v1/items/by-barcode/0000000000000');

        $response->assertNotFound();
        $response->assertJson([
            'error' => 'Item not found',
            'message' => 'Barcode not found.',
        ]);
    }

    public function test_rejects_duplicate_barcode_on_another_item(): void
    {
        $firstItemResponse = $this->postJson('/api/v1/items/create', [
            'name' => 'Producto A',
        ]);
        $secondItemResponse = $this->postJson('/api/v1/items/create', [
            'name' => 'Producto B',
        ]);

        $firstItemId = $firstItemResponse->json('data.id');
        $secondItemId = $secondItemResponse->json('data.id');

        $this->postJson("/api/v1/items/{$firstItemId}/identifiers/create", [
            'type' => 'upc',
            'value' => '123456789012',
            'is_primary' => true,
        ])->assertCreated();

        $duplicateResponse = $this->postJson("/api/v1/items/{$secondItemId}/identifiers/create", [
            'type' => 'upc',
            'value' => '123456789012',
            'is_primary' => true,
        ]);

        $duplicateResponse->assertStatus(409);
        $duplicateResponse->assertJson([
            'error' => 'Unable to create identifier',
            'message' => 'The identifier value is already assigned to another item.',
        ]);
    }
}
