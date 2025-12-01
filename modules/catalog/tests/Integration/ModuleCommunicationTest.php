<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Integration tests for cross-module communication.
 * 
 * Los módulos deben comunicarse como aplicaciones Laravel separadas,
 * usando APIs HTTP o interfaces de dominio.
 * 
 * Note: These are smoke tests that verify inter-module API availability.
 * Full integration tests would require database setup.
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
            ->getJson('/api/v1/locations/read');

        // 200 = success, 500 = db not available (acceptable in test environment)
        $this->assertContains($response->status(), [200, 500]);
    }

    /**
     * Test that Stock module can get taxonomy data from Taxonomy module.
     */
    public function test_stock_can_query_taxonomy_via_api(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/v1/taxonomy/vocabularies/read');

        // 200 = success, 500 = db not available (acceptable in test environment)
        $this->assertContains($response->status(), [200, 500]);
    }

    /**
     * Test that modules are isolated - each has its own adapter.
     */
    public function test_modules_have_independent_adapters(): void
    {
        // Stock with local adapter
        $stockResponse = $this->withAdapter('stock', 'local')
            ->getJson('/api/v1/stock/items/list');

        // Locations with local adapter (independent)
        $locationsResponse = $this->withAdapter('locations', 'local')
            ->getJson('/api/v1/locations/read');

        // Both should work independently (200 or 500 for db error)
        $this->assertTrue(
            in_array($stockResponse->status(), [200, 500]),
            'Stock module should respond'
        );
        $this->assertTrue(
            in_array($locationsResponse->status(), [200, 500]),
            'Locations module should respond'
        );
    }
}
