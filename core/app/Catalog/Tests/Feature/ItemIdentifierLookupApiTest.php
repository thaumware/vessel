<?php

namespace App\Catalog\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemIdentifierLookupApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_find_a_product_by_manual_identifier(): void
    {
        $createResponse = $this->postJson('/api/v1/items/create', [
            'name' => 'Producto por SKU',
            'status' => 'active',
        ]);

        $createResponse->assertCreated();
        $itemId = $createResponse->json('data.id');

        $identifierResponse = $this->postJson("/api/v1/items/{$itemId}/identifiers/create", [
            'type' => 'sku',
            'value' => 'SKU-001',
            'is_primary' => true,
        ]);

        $identifierResponse->assertCreated();
        $identifierResponse->assertJsonPath('data.type', 'sku');
        $identifierResponse->assertJsonPath('data.value', 'SKU-001');

        $lookupResponse = $this->getJson('/api/v1/items/by-identifier/SKU-001');

        $lookupResponse->assertOk();
        $lookupResponse->assertJsonPath('data.id', $itemId);
        $lookupResponse->assertJsonPath('data.name', 'Producto por SKU');
        $lookupResponse->assertJsonPath('data.identifier.type', 'sku');
        $lookupResponse->assertJsonPath('data.identifier.value', 'SKU-001');
    }
}
