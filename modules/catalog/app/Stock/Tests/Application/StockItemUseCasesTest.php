<?php

namespace App\Stock\Tests\Application;

use App\Stock\Application\UseCases\CreateStockItem;
use App\Stock\Application\UseCases\GetStockItem;
use App\Stock\Application\UseCases\ListStockItems;
use App\Stock\Application\UseCases\UpdateStockItem;
use App\Stock\Application\UseCases\DeleteStockItem;
use App\Stock\Application\UseCases\AdjustStockQuantity;
use App\Stock\Application\UseCases\ReserveStock;
use App\Stock\Application\UseCases\ReleaseStock;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Tests\StockTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class StockItemUseCasesTest extends StockTestCase
{
    private MockObject|StockItemRepositoryInterface $repository;
    private MockObject|CatalogGatewayInterface $catalogGateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = $this->createMock(StockItemRepositoryInterface::class);
        $this->catalogGateway = $this->createMock(CatalogGatewayInterface::class);
        
        // Default mock behavior for catalog gateway
        $this->catalogGateway->method('getDefaultOriginName')->willReturn('internal_catalog');
    }

    // === CreateStockItem Tests ===

    public function test_create_stock_item_persists_and_links_to_catalog(): void
    {
        $id = $this->generateUuid();
        $data = [
            'id' => $id,
            'sku' => 'NEW-SKU-001',
            'catalog_item_id' => $this->generateUuid(),
            'catalog_origin' => 'internal_catalog',
            'location_id' => $this->generateUuid(),
            'location_type' => 'warehouse',
            'quantity' => 100,
        ];

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn(StockItem $item) => $item);

        $this->catalogGateway
            ->expects($this->once())
            ->method('linkToCatalog');

        $useCase = new CreateStockItem($this->repository, $this->catalogGateway);
        $result = $useCase->execute($data);

        $this->assertEquals($id, $result->getId());
        $this->assertEquals($data['sku'], $result->getSku());
        $this->assertEquals($data['catalog_item_id'], $result->getCatalogItemId());
        $this->assertEquals($data['quantity'], $result->getQuantity());
    }

    public function test_create_stock_item_throws_exception_when_id_not_provided(): void
    {
        $data = [
            'sku' => 'NEW-SKU',
            'catalog_item_id' => $this->generateUuid(),
            'location_id' => $this->generateUuid(),
        ];

        $useCase = new CreateStockItem($this->repository, $this->catalogGateway);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID is required');
        
        $useCase->execute($data);
    }

    // === GetStockItem Tests ===

    public function test_get_stock_item_returns_item_when_found(): void
    {
        $id = $this->generateUuid();
        $stockItem = new StockItem(
            id: $id,
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'internal_catalog',
            locationId: $this->generateUuid(),
        );

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn($stockItem);

        $useCase = new GetStockItem($this->repository, $this->catalogGateway);
        $result = $useCase->execute($id);

        $this->assertNotNull($result);
        $this->assertEquals($id, $result->getId());
    }

    public function test_get_stock_item_returns_null_when_not_found(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->method('findById')
            ->willReturn(null);

        $useCase = new GetStockItem($this->repository, $this->catalogGateway);
        $result = $useCase->execute($id);

        $this->assertNull($result);
    }

    // === ListStockItems Tests ===

    public function test_list_stock_items_returns_filtered_results(): void
    {
        $locationId = $this->generateUuid();
        $items = [
            new StockItem(
                id: $this->generateUuid(),
                sku: 'SKU-001',
                catalogItemId: $this->generateUuid(),
                catalogOrigin: 'catalog_items',
                locationId: $locationId,
            ),
            new StockItem(
                id: $this->generateUuid(),
                sku: 'SKU-002',
                catalogItemId: $this->generateUuid(),
                catalogOrigin: 'catalog_items',
                locationId: $locationId,
            ),
        ];

        $this->repository
            ->expects($this->once())
            ->method('search')
            ->with(['location_id' => $locationId], 50, 0)
            ->willReturn($items);

        $useCase = new ListStockItems($this->repository, $this->catalogGateway);
        $result = $useCase->execute(['location_id' => $locationId]);

        $this->assertCount(2, $result);
    }

    // === UpdateStockItem Tests ===

    public function test_update_stock_item_modifies_and_persists(): void
    {
        $id = $this->generateUuid();
        $existing = new StockItem(
            id: $id,
            sku: 'OLD-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 50,
        );

        $this->repository
            ->method('findById')
            ->with($id)
            ->willReturn($existing);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->willReturnCallback(fn(StockItem $item) => $item);

        $useCase = new UpdateStockItem($this->repository);
        $result = $useCase->execute($id, ['sku' => 'NEW-SKU', 'quantity' => 100]);

        $this->assertEquals('NEW-SKU', $result->getSku());
        $this->assertEquals(100, $result->getQuantity());
    }

    public function test_update_stock_item_throws_when_not_found(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $useCase = new UpdateStockItem($this->repository);

        $this->expectException(\RuntimeException::class);
        $useCase->execute($this->generateUuid(), ['sku' => 'NEW-SKU']);
    }

    // === DeleteStockItem Tests ===

    public function test_delete_stock_item_calls_repository(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id);

        $useCase = new DeleteStockItem($this->repository);
        $useCase->execute($id);

        // No exception = success
        $this->assertTrue(true);
    }

    // === AdjustStockQuantity Tests ===

    public function test_adjust_stock_quantity_adds_delta(): void
    {
        $sku = 'TEST-SKU';
        $locationId = $this->generateUuid();
        
        $adjusted = new StockItem(
            id: $this->generateUuid(),
            sku: $sku,
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $locationId,
            quantity: 150,
        );

        $this->repository
            ->expects($this->once())
            ->method('adjustQuantity')
            ->with($sku, $locationId, 50)
            ->willReturn($adjusted);

        $useCase = new AdjustStockQuantity($this->repository);
        $result = $useCase->execute($sku, $locationId, 50);

        $this->assertEquals(150, $result->getQuantity());
    }

    // === ReserveStock Tests ===

    public function test_reserve_stock_increases_reserved_quantity(): void
    {
        $id = $this->generateUuid();
        
        $reserved = new StockItem(
            id: $id,
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 25,
        );

        $this->repository
            ->expects($this->once())
            ->method('reserve')
            ->with($id, 25)
            ->willReturn($reserved);

        $useCase = new ReserveStock($this->repository);
        $result = $useCase->execute($id, 25);

        $this->assertEquals(25, $result->getReservedQuantity());
    }

    // === ReleaseStock Tests ===

    public function test_release_stock_decreases_reserved_quantity(): void
    {
        $id = $this->generateUuid();
        
        $released = new StockItem(
            id: $id,
            sku: 'TEST-SKU',
            catalogItemId: $this->generateUuid(),
            catalogOrigin: 'catalog_items',
            locationId: $this->generateUuid(),
            quantity: 100,
            reservedQuantity: 15,
        );

        $this->repository
            ->expects($this->once())
            ->method('release')
            ->with($id, 10)
            ->willReturn($released);

        $useCase = new ReleaseStock($this->repository);
        $result = $useCase->execute($id, 10);

        $this->assertEquals(15, $result->getReservedQuantity());
    }
}
