<?php

namespace App\Stock\Tests\Application;

use App\Stock\Application\UseCases\GetLocationStockSummary\GetLocationStockSummaryUseCase;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Tests\StockTestCase;

/**
 * Test que verifica comportamiento condicional basado en módulos activos.
 */
class ModuleConditionalLoadingTest extends StockTestCase
{
    public function test_summary_funciona_sin_catalog_module(): void
    {
        // Simular que Catalog NO está activo
        $catalogGateway = $this->createCatalogGatewayMock();
        $locationGateway = $this->createLocationGatewayMock();
        
        $stockRepo = $this->createMock(StockItemRepositoryInterface::class);
        $itemId = $this->generateUuid();
        $locationId = $this->generateUuid();
        
        $stockRepo->method('findByLocation')
            ->willReturn([
                new StockItem(
                    id: $this->generateUuid(),
                    itemId: $itemId,
                    locationId: $locationId,
                    quantity: 100
                ),
            ]);

        $useCase = new GetLocationStockSummaryUseCase(
            $stockRepo,
            $locationGateway,
            $catalogGateway
        );

        $result = $useCase->execute($locationId);

        // Debe funcionar sin catalog
        $this->assertEquals(1, $result['total_items']);
        $this->assertEquals(100, $result['items'][0]['total_quantity']);
        
        // El item_id debe estar presente siempre
        $this->assertEquals($itemId, $result['items'][0]['item_id']);
    }

    public function test_catalog_gateway_mock_se_adapta_a_modulo_activo(): void
    {
        $gateway = $this->createCatalogGatewayMock();
        
        $itemId = $this->generateUuid();
        $stockItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            locationId: $this->generateUuid(),
            quantity: 50
        );

        $enriched = $gateway->attachCatalogData([$stockItem]);

        $this->assertIsArray($enriched);
        $this->assertCount(1, $enriched);
        $this->assertEquals($itemId, $enriched[0]['item_id']);

        // Si Catalog está activo, debe tener enriquecimiento
        $this->whenModuleEnabled('catalog', function () use ($enriched) {
            $this->assertArrayHasKey('catalog_item', $enriched[0]);
            $this->assertArrayHasKey('name', $enriched[0]['catalog_item']);
            $this->assertArrayHasKey('uom_symbol', $enriched[0]['catalog_item']);
        });
    }

    public function test_puede_detectar_modulos_activos(): void
    {
        // En tests unitarios, por defecto todos están "activos"
        $this->assertTrue($this->isModuleEnabled('stock'));
        $this->assertTrue($this->isModuleEnabled('catalog'));
        $this->assertTrue($this->isModuleEnabled('locations'));
    }

    public function test_require_module_no_falla_en_tests_unitarios(): void
    {
        // No debe skip porque en tests unitarios se asume que están activos
        $this->requireModule('catalog');
        
        // Si llegamos aquí, el test no fue skipped
        $this->assertTrue(true);
    }

    public function test_when_module_enabled_ejecuta_callback(): void
    {
        $executed = false;
        
        $this->whenModuleEnabled('catalog', function () use (&$executed) {
            $executed = true;
            return 'test-value';
        });

        // En tests unitarios, el callback debe ejecutarse
        $this->assertTrue($executed);
    }

    public function test_location_gateway_mock_retorna_descendientes(): void
    {
        $childId1 = $this->generateUuid();
        $childId2 = $this->generateUuid();
        
        $gateway = $this->createLocationGatewayMock([$childId1, $childId2]);

        $descendants = $gateway->getDescendantIds('any-id');

        $this->assertCount(2, $descendants);
        $this->assertContains($childId1, $descendants);
        $this->assertContains($childId2, $descendants);
    }
}
