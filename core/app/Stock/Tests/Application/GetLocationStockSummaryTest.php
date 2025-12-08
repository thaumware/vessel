<?php

namespace App\Stock\Tests\Application;

use App\Stock\Application\UseCases\GetLocationStockSummary\GetLocationStockSummaryUseCase;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Tests\StockTestCase;

class GetLocationStockSummaryTest extends StockTestCase
{
    private GetLocationStockSummaryUseCase $useCase;
    private StockItemRepositoryInterface $stockRepo;
    private LocationGatewayInterface $locationGateway;
    private CatalogGatewayInterface $catalogGateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->stockRepo = $this->createMock(StockItemRepositoryInterface::class);
        $this->locationGateway = $this->createLocationGatewayMock();
        $this->catalogGateway = $this->createCatalogGatewayMock();
        
        $this->useCase = new GetLocationStockSummaryUseCase(
            $this->stockRepo,
            $this->locationGateway,
            $this->catalogGateway
        );
    }

    public function test_suma_stock_de_ubicacion_sin_hijos(): void
    {
        $locationId = $this->generateUuid();
        $itemId1 = $this->generateUuid();
        $itemId2 = $this->generateUuid();

        // Mock: stock en la ubicación
        $this->stockRepo
            ->method('findByLocation')
            ->with($locationId)
            ->willReturn([
                new StockItem(
                    id: $this->generateUuid(),
                    itemId: $itemId1,
                    locationId: $locationId,
                    quantity: 100,
                    reservedQuantity: 20
                ),
                new StockItem(
                    id: $this->generateUuid(),
                    itemId: $itemId2,
                    locationId: $locationId,
                    quantity: 50,
                    reservedQuantity: 0
                ),
            ]);

        $result = $this->useCase->execute($locationId, includeChildren: true);

        $this->assertEquals($locationId, $result['location_id']);
        $this->assertTrue($result['includes_children']);
        $this->assertEquals(1, $result['total_locations']);
        $this->assertEquals(2, $result['total_items']);
        
        // Verificar items agrupados
        $items = $result['items'];
        $this->assertCount(2, $items);
        
        $item1 = collect($items)->firstWhere('item_id', $itemId1);
        $this->assertEquals(100, $item1['total_quantity']);
        $this->assertEquals(20, $item1['reserved_quantity']);
        $this->assertEquals(80, $item1['available_quantity']);
        $this->assertEquals([$locationId], $item1['locations']);
        
        // Si Catalog está activo, debe tener enriquecimiento
        $this->whenModuleEnabled('catalog', function () use ($item1) {
            $this->assertArrayHasKey('catalog_item', $item1);
            $this->assertIsArray($item1['catalog_item']);
        });
    }

    public function test_suma_stock_de_ubicacion_con_hijos(): void
    {
        $mainLocationId = $this->generateUuid();
        $shelfId1 = $this->generateUuid();
        $shelfId2 = $this->generateUuid();
        $itemId = $this->generateUuid();

        // Recrear el useCase con gateway que tiene hijos
        $this->locationGateway = $this->createLocationGatewayMock([$shelfId1, $shelfId2]);
        $this->useCase = new GetLocationStockSummaryUseCase(
            $this->stockRepo,
            $this->locationGateway,
            $this->catalogGateway
        );

        // Mock: stock distribuido en las 3 ubicaciones
        $this->stockRepo
            ->method('findByLocation')
            ->willReturnCallback(function ($locId) use ($mainLocationId, $shelfId1, $shelfId2, $itemId) {
                if ($locId === $mainLocationId) {
                    return [
                        new StockItem(
                            id: $this->generateUuid(),
                            itemId: $itemId,
                            locationId: $mainLocationId,
                            quantity: 50
                        ),
                    ];
                }
                if ($locId === $shelfId1) {
                    return [
                        new StockItem(
                            id: $this->generateUuid(),
                            itemId: $itemId,
                            locationId: $shelfId1,
                            quantity: 30
                        ),
                    ];
                }
                if ($locId === $shelfId2) {
                    return [
                        new StockItem(
                            id: $this->generateUuid(),
                            itemId: $itemId,
                            locationId: $shelfId2,
                            quantity: 20
                        ),
                    ];
                }
                return [];
            });

        $result = $this->useCase->execute($mainLocationId);

        $this->assertEquals(3, $result['total_locations']);
        $this->assertEquals(1, $result['total_items']);
        
        $item = $result['items'][0];
        $this->assertEquals(100, $item['total_quantity']); // 50 + 30 + 20
        $this->assertCount(3, $item['locations']);
        
        // Verificar enriquecimiento si Catalog está activo
        $this->whenModuleEnabled('catalog', function () use ($item) {
            $this->assertArrayHasKey('catalog_item', $item);
            $this->assertArrayHasKey('name', $item['catalog_item']);
            $this->assertArrayHasKey('uom_symbol', $item['catalog_item']);
        });
    }

    public function test_puede_excluir_hijos(): void
    {
        $locationId = $this->generateUuid();
        
        // Recrear con locationGateway sin hijos
        $locationGateway = $this->createLocationGatewayMock([]);
        $stockRepo = $this->createMock(StockItemRepositoryInterface::class);
        $stockRepo->method('findByLocation')->willReturn([]);
        
        $useCase = new GetLocationStockSummaryUseCase(
            $stockRepo,
            $locationGateway,
            $this->catalogGateway
        );

        $result = $useCase->execute($locationId, includeChildren: false);

        $this->assertFalse($result['includes_children']);
        $this->assertEquals(1, $result['total_locations']);
    }
}
