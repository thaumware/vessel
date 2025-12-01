<?php

namespace App\Stock\Tests\Infrastructure;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Infrastructure\Out\InMemory\InMemoryStockItemRepository;
use App\Stock\Tests\StockTestCase;
use DomainException;

class InMemoryStockItemRepositoryTest extends StockTestCase
{
    private InMemoryStockItemRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        // Create repository without loading data from file
        $this->repository = new InMemoryStockItemRepository(loadFromFile: false);
    }

    public function test_save_and_find_by_id(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU-001',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
        );

        $saved = $this->repository->save($item);
        $found = $this->repository->findById($item->getId());

        $this->assertEquals($item->getId(), $saved->getId());
        $this->assertNotNull($found);
        $this->assertEquals($item->getSku(), $found->getSku());
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $found = $this->repository->findById($this->generateUuid());
        $this->assertNull($found);
    }

    public function test_find_by_sku(): void
    {
        $sku = 'UNIQUE-SKU';
        $locationId1 = $this->generateUuid();
        $locationId2 = $this->generateUuid();

        $item1 = new StockItem(
            id: $this->generateUuid(),
            sku: $sku,
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $locationId1,
            quantity: 50,
        );

        $item2 = new StockItem(
            id: $this->generateUuid(),
            sku: $sku,
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $locationId2,
            quantity: 75,
        );

        $this->repository->save($item1);
        $this->repository->save($item2);

        $found = $this->repository->findBySku($sku);

        $this->assertCount(2, $found);
    }

    public function test_find_by_sku_and_location(): void
    {
        $sku = 'TARGET-SKU';
        $locationId = $this->generateUuid();

        $item = new StockItem(
            id: $this->generateUuid(),
            sku: $sku,
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $locationId,
            quantity: 100,
        );

        $this->repository->save($item);

        $found = $this->repository->findBySkuAndLocation($sku, $locationId);

        $this->assertNotNull($found);
        $this->assertEquals($sku, $found->getSku());
        $this->assertEquals($locationId, $found->getLocationId());
    }

    public function test_find_by_location(): void
    {
        $locationId = $this->generateUuid();

        for ($i = 0; $i < 3; $i++) {
            $this->repository->save(new StockItem(
                id: $this->generateUuid(),
                sku: "SKU-{$i}",
                catalogItemId: $this->generateUuid(),
                catalogOrigin: 'catalog_items',
                locationId: $locationId,
            ));
        }

        // Different location
        $this->repository->save(new StockItem(
            id: $this->generateUuid(),
            sku: 'SKU-OTHER',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
        ));

        $found = $this->repository->findByLocation($locationId);

        $this->assertCount(3, $found);
    }

    public function test_search_with_filters(): void
    {
        $catalogOrigin = 'external_api';

        $this->repository->save(new StockItem(
            id: $this->generateUuid(),
            sku: 'EXT-001',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: $catalogOrigin,
            locationId: $this->generateUuid(),
        ));

        $this->repository->save(new StockItem(
            id: $this->generateUuid(),
            sku: 'INT-001',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
        ));

        $results = $this->repository->search(['catalog_origin' => $catalogOrigin]);

        $this->assertCount(1, $results);
        $this->assertEquals('EXT-001', $results[0]->getSku());
    }

    public function test_search_with_pagination(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->repository->save(new StockItem(
                id: $this->generateUuid(),
                sku: "SKU-{$i}",
                catalogItemId: $this->generateUuid(),
                catalogOrigin: 'catalog_items',
                locationId: $this->generateUuid(),
            ));
        }

        $page1 = $this->repository->search([], 3, 0);
        $page2 = $this->repository->search([], 3, 3);
        $page3 = $this->repository->search([], 3, 6);
        $page4 = $this->repository->search([], 3, 9);

        $this->assertCount(3, $page1);
        $this->assertCount(3, $page2);
        $this->assertCount(3, $page3);
        $this->assertCount(1, $page4);
    }

    public function test_update_modifies_existing_item(): void
    {
        $id = $this->generateUuid();
        $item = new StockItem(
            id: $id,
            sku: 'ORIGINAL-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
        );

        $this->repository->save($item);

        $updated = new StockItem(
            id: $id,
            sku: 'UPDATED-SKU',
            catalogItemId: $item->getCatalogItemId(),
            catalogOrigin: $item->getCatalogOrigin(),
            locationId: $item->getLocationId(),
            quantity: 200,
        );

        $this->repository->update($updated);

        $found = $this->repository->findById($id);

        $this->assertEquals('UPDATED-SKU', $found->getSku());
        $this->assertEquals(200, $found->getQuantity());
    }

    public function test_delete_removes_item(): void
    {
        $id = $this->generateUuid();
        $item = new StockItem(
            id: $id,
            sku: 'TO-DELETE',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
        );

        $this->repository->save($item);
        $this->assertNotNull($this->repository->findById($id));

        $this->repository->delete($id);
        $this->assertNull($this->repository->findById($id));
    }

    public function test_adjust_quantity(): void
    {
        $sku = 'ADJUST-SKU';
        $locationId = $this->generateUuid();

        $item = new StockItem(
            id: $this->generateUuid(),
            sku: $sku,
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $locationId,
            quantity: 100,
        );

        $this->repository->save($item);

        $adjusted = $this->repository->adjustQuantity($sku, $locationId, 50);
        $this->assertEquals(150, $adjusted->getQuantity());

        $adjusted = $this->repository->adjustQuantity($sku, $locationId, -30);
        $this->assertEquals(120, $adjusted->getQuantity());
    }

    public function test_adjust_quantity_throws_when_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->repository->adjustQuantity('NONEXISTENT', $this->generateUuid(), 10);
    }

    public function test_reserve(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'RESERVE-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 0,
        );

        $this->repository->save($item);

        $reserved = $this->repository->reserve($item->getId(), 25);

        $this->assertEquals(25, $reserved->getReservedQuantity());
        $this->assertEquals(75, $reserved->getAvailableQuantity());
    }

    public function test_reserve_allows_negative_available(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'LIMITED-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 50,
            reservedQuantity: 30,
        );

        $this->repository->save($item);

        // Permite reservar más de lo disponible
        $reserved = $this->repository->reserve($item->getId(), 25);
        
        $this->assertEquals(55, $reserved->getReservedQuantity());
        $this->assertEquals(-5, $reserved->getAvailableQuantity());
    }

    public function test_release(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'RELEASE-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 50,
        );

        $this->repository->save($item);

        $released = $this->repository->release($item->getId(), 20);

        $this->assertEquals(30, $released->getReservedQuantity());
        $this->assertEquals(70, $released->getAvailableQuantity());
    }

    public function test_release_allows_negative_reserved(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'OVER-RELEASE',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 10,
        );

        $this->repository->save($item);

        // Permite liberar más de lo reservado
        $released = $this->repository->release($item->getId(), 20);
        
        $this->assertEquals(-10, $released->getReservedQuantity());
        $this->assertEquals(110, $released->getAvailableQuantity());
    }
}
