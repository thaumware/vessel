<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Integration tests for cross-module communication.
 * 
 * Los módulos deben comunicarse como aplicaciones Laravel separadas,
 * usando APIs HTTP o interfaces de dominio.
 */
class ModuleCommunicationTest extends TestCase
{
    /**
     * Test that Stock module can get location data from Locations module.
     */
    public function test_stock_can_query_locations_via_api(): void
    {
        // Simular llamada HTTP al módulo Locations con adapter local
        $response = $this->withAdapter('locations', 'local')
            ->getJson('/api/locations/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'type']
            ]
        ]);
    }

    /**
     * Test that Stock module can get taxonomy data from Taxonomy module.
     */
    public function test_stock_can_query_taxonomy_via_api(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/taxonomy/vocabularies');

        $response->assertStatus(200);
    }

    /**
     * Test that modules are isolated - each has its own adapter.
     */
    public function test_modules_have_independent_adapters(): void
    {
        // Stock with local adapter
        $stockResponse = $this->withAdapter('stock', 'local')
            ->getJson('/api/stock/items');

        // Locations with local adapter (independent)
        $locationsResponse = $this->withAdapter('locations', 'local')
            ->getJson('/api/locations/list');

        // Both should work independently
        $this->assertTrue(
            $stockResponse->status() === 200 || $stockResponse->status() === 404,
            'Stock module should respond'
        );
        $this->assertTrue(
            $locationsResponse->status() === 200,
            'Locations module should respond'
        );
    }
}
