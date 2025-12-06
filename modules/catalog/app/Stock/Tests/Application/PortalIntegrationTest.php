<?php

namespace App\Stock\Tests\Application;

use App\Stock\Tests\StockTestCase;
use App\Stock\Tests\Support\PortalJsonAdapter;
use Thaumware\Portal\Core\PortalRegistry;
use Thaumware\Portal\Core\RelationLoader;

class PortalIntegrationTest extends StockTestCase
{
    public function test_enriches_items_with_portal_catalog_fixture(): void
    {
        $portalAutoload = dirname(__DIR__, 6) . '/packages/portal/vendor/autoload.php';
        if (!class_exists(PortalRegistry::class)) {
            $this->assertFileExists($portalAutoload, "Portal autoload not found: {$portalAutoload}");
            require_once $portalAutoload;
        }

        $fixture = __DIR__ . '/../Support/data/portal_catalog.json';
        $adapter = new PortalJsonAdapter($fixture, workspaceId: 'plant-01');

        $registry = new PortalRegistry($adapter);
        $loader = new RelationLoader($adapter, $adapter);

        $registry->register('catalog_items', 'portal_catalog', 'fixture');

        $registry->link('BEARING-6204', 'StockItem', 'catalog_items', 'BEARING-6204', ['location_id' => 'WH-MAIN', 'workspace_id' => 'plant-01']);
        $registry->link('OIL-ISO46', 'StockItem', 'catalog_items', 'OIL-ISO46', ['location_id' => 'WH-MAIN', 'workspace_id' => 'plant-01']);

        $models = [
            (object)['id' => 'BEARING-6204', 'location_id' => 'WH-MAIN'],
            (object)['id' => 'OIL-ISO46', 'location_id' => 'WH-MAIN'],
        ];

        $enriched = $loader->attach($models);

        $bearing = $enriched[0]->portal_catalog_items ?? null;
        $oil = $enriched[1]->portal_catalog_items ?? null;

        $this->assertNotNull($bearing, 'Bearing should be enriched');
        $this->assertSame('ea', $bearing['uom']);
        $this->assertContains('rodamientos', $bearing['taxonomy']['categorias']);
        $this->assertSame('ACME Bearings', $bearing['metadata']['supplier']);

        $this->assertNotNull($oil, 'Oil should be enriched');
        $this->assertSame('kg', $oil['uom']);
        $this->assertTrue($oil['metadata']['hazmat']);
        $this->assertContains('lubricantes', $oil['taxonomy']['categorias']);
    }
}
