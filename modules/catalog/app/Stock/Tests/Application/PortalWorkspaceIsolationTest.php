<?php

namespace App\Stock\Tests\Application;

use App\Stock\Tests\StockTestCase;
use App\Stock\Tests\Support\PortalJsonAdapter;
use Thaumware\Portal\Core\PortalRegistry;
use Thaumware\Portal\Core\RelationLoader;

class PortalWorkspaceIsolationTest extends StockTestCase
{
    public function test_does_not_attach_cross_workspace_relations(): void
    {
        $portalAutoload = dirname(__DIR__, 6) . '/packages/portal/vendor/autoload.php';
        if (!class_exists(PortalRegistry::class)) {
            $this->assertFileExists($portalAutoload, "Portal autoload not found: {$portalAutoload}");
            require_once $portalAutoload;
        }

        $fixture = __DIR__ . '/../Support/data/portal_catalog.json';

        // Workspace A adapter (active tenant)
        $adapterA = new PortalJsonAdapter($fixture, workspaceId: 'plant-01');
        $registryA = new PortalRegistry($adapterA);
        $loaderA = new RelationLoader($adapterA, $adapterA);
        $registryA->register('catalog_items', 'portal_catalog', 'fixture');
        $registryA->link('BEARING-6204', 'StockItem', 'catalog_items', 'BEARING-6204', ['workspace_id' => 'plant-01']);

        // Workspace B adapter simulates another tenant writing links
        $adapterB = new PortalJsonAdapter($fixture, workspaceId: 'plant-02');
        $registryB = new PortalRegistry($adapterB);
        $registryB->register('catalog_items', 'portal_catalog', 'fixture');
        $registryB->link('OIL-ISO46', 'StockItem', 'catalog_items', 'OIL-ISO46', ['workspace_id' => 'plant-02']);

        $models = [
            (object)['id' => 'BEARING-6204'],
            (object)['id' => 'OIL-ISO46'],
        ];

        $enriched = $loaderA->attach($models);

        $this->assertNotNull($enriched[0]->portal_catalog_items ?? null, 'Workspace A item should attach');
        $this->assertNull($enriched[1]->portal_catalog_items ?? null, 'Cross-workspace relation must be blocked');
    }
}
