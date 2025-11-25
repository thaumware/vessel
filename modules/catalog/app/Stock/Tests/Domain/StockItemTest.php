<?php

namespace App\Stock\Tests\Domain;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;
use DomainException;

class StockItemTest extends StockTestCase
{
    public function test_can_create_stock_item(): void
    {
        $data = $this->createStockItemData();
        
        $item = new StockItem(
            id: $data['id'],
            sku: $data['sku'],
            catalogItemId: $data['catalogItemId'],
            catalogOrigin: $data['catalogOrigin'],
            locationId: $data['locationId'],
            locationType: $data['locationType'],
            quantity: $data['quantity'],
            reservedQuantity: $data['reservedQuantity'],
        );

        $this->assertEquals($data['id'], $item->getId());
        $this->assertEquals($data['sku'], $item->getSku());
        $this->assertEquals($data['catalogItemId'], $item->getCatalogItemId());
        $this->assertEquals($data['catalogOrigin'], $item->getCatalogOrigin());
        $this->assertEquals($data['locationId'], $item->getLocationId());
        $this->assertEquals($data['locationType'], $item->getLocationType());
        $this->assertEquals($data['quantity'], $item->getQuantity());
        $this->assertEquals($data['reservedQuantity'], $item->getReservedQuantity());
    }

    public function test_calculates_available_quantity(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 30,
        );

        $this->assertEquals(70, $item->getAvailableQuantity());
    }

    public function test_has_available_stock_returns_true_when_sufficient(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 30,
        );

        $this->assertTrue($item->hasAvailableStock(70));
        $this->assertTrue($item->hasAvailableStock(50));
        $this->assertFalse($item->hasAvailableStock(71));
    }

    public function test_reserve_reduces_available_quantity(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 0,
        );

        $reserved = $item->reserve(25);

        $this->assertEquals(25, $reserved->getReservedQuantity());
        $this->assertEquals(75, $reserved->getAvailableQuantity());
        // Original should be unchanged (immutability)
        $this->assertEquals(0, $item->getReservedQuantity());
    }

    public function test_reserve_throws_when_insufficient_stock(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 50,
            reservedQuantity: 30,
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot reserve 25 units. Only 20 available.');
        
        $item->reserve(25);
    }

    public function test_release_increases_available_quantity(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 50,
        );

        $released = $item->release(20);

        $this->assertEquals(30, $released->getReservedQuantity());
        $this->assertEquals(70, $released->getAvailableQuantity());
    }

    public function test_release_throws_when_releasing_more_than_reserved(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 10,
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot release 15 units. Only 10 reserved.');
        
        $item->release(15);
    }

    public function test_adjust_quantity_adds_delta(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
        );

        $adjusted = $item->adjustQuantity(50);
        $this->assertEquals(150, $adjusted->getQuantity());

        $adjusted = $item->adjustQuantity(-30);
        $this->assertEquals(70, $adjusted->getQuantity());
    }

    public function test_is_expired_returns_correct_value(): void
    {
        // Not expired
        $futureDate = new DateTimeImmutable('+30 days');
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            expirationDate: $futureDate,
        );
        $this->assertFalse($item->isExpired());

        // Expired
        $pastDate = new DateTimeImmutable('-1 day');
        $expiredItem = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            expirationDate: $pastDate,
        );
        $this->assertTrue($expiredItem->isExpired());

        // No expiration date
        $noExpiry = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
        );
        $this->assertFalse($noExpiry->isExpired());
    }

    public function test_is_lot_tracked(): void
    {
        $tracked = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            lotNumber: 'LOT-001',
        );
        $this->assertTrue($tracked->isLotTracked());

        $notTracked = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
        );
        $this->assertFalse($notTracked->isLotTracked());
    }

    public function test_is_serial_tracked(): void
    {
        $tracked = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            serialNumber: 'SN-12345',
        );
        $this->assertTrue($tracked->isSerialTracked());

        $notTracked = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
        );
        $this->assertFalse($notTracked->isSerialTracked());
    }

    public function test_to_array_uses_snake_case(): void
    {
        $item = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: 'cat-123',
            catalogOrigin: 'catalog_items',
            locationId: 'loc-456',
            locationType: 'warehouse',
            quantity: 100,
            reservedQuantity: 25,
            lotNumber: 'LOT-001',
            workspaceId: 'ws-789',
        );

        $array = $item->toArray();

        $this->assertArrayHasKey('catalog_item_id', $array);
        $this->assertArrayHasKey('catalog_origin', $array);
        $this->assertArrayHasKey('location_id', $array);
        $this->assertArrayHasKey('location_type', $array);
        $this->assertArrayHasKey('reserved_quantity', $array);
        $this->assertArrayHasKey('available_quantity', $array);
        $this->assertArrayHasKey('lot_number', $array);
        $this->assertArrayHasKey('workspace_id', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
        
        $this->assertEquals(75, $array['available_quantity']);
    }

    public function test_immutability_on_mutations(): void
    {
        $original = new StockItem(
            id: $this->generateUuid(),
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 0,
        );

        $withQuantity = $original->withQuantity(200);
        $withReserved = $original->withReservedQuantity(50);
        $adjusted = $original->adjustQuantity(25);
        $reserved = $original->reserve(10);

        // Original unchanged
        $this->assertEquals(100, $original->getQuantity());
        $this->assertEquals(0, $original->getReservedQuantity());

        // New instances have changes
        $this->assertEquals(200, $withQuantity->getQuantity());
        $this->assertEquals(50, $withReserved->getReservedQuantity());
        $this->assertEquals(125, $adjusted->getQuantity());
        $this->assertEquals(10, $reserved->getReservedQuantity());
    }
}
