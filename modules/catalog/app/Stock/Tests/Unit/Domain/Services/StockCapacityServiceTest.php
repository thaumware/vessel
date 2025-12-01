<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\Services;

use App\Stock\Domain\Entities\LocationStockSettings;
use App\Stock\Domain\Entities\Stock;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use App\Stock\Domain\Interfaces\StockRepositoryInterface;
use App\Stock\Domain\Services\StockCapacityService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockCapacityServiceTest extends TestCase
{
    private StockCapacityService $service;
    private MockObject&LocationStockSettingsRepositoryInterface $settingsRepository;
    private MockObject&LocationGatewayInterface $locationGateway;
    private MockObject&StockRepositoryInterface $stockRepository;

    protected function setUp(): void
    {
        $this->settingsRepository = $this->createMock(LocationStockSettingsRepositoryInterface::class);
        $this->locationGateway = $this->createMock(LocationGatewayInterface::class);
        $this->stockRepository = $this->createMock(StockRepositoryInterface::class);

        $this->service = new StockCapacityService(
            $this->settingsRepository,
            $this->locationGateway,
            $this->stockRepository
        );
    }

    public function test_can_accept_stock_without_settings(): void
    {
        $locationId = 'loc-1';
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->with($locationId)
            ->willReturn(null);

        $result = $this->service->canAcceptStock($locationId, 100);

        $this->assertTrue($result->isValid());
    }

    public function test_can_accept_stock_when_inactive_settings(): void
    {
        $locationId = 'loc-1';
        $settings = new LocationStockSettings(
            id: 'settings-1',
            locationId: $locationId,
            maxQuantity: 100,
            isActive: false
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $result = $this->service->canAcceptStock($locationId, 50);

        $this->assertFalse($result->isValid());
        $this->assertEquals('LOCATION_NOT_ACTIVE', $result->getErrorCode());
    }

    public function test_can_accept_stock_within_max_quantity(): void
    {
        $locationId = 'loc-1';
        $settings = LocationStockSettings::createWithCapacity(
            id: 'settings-1',
            locationId: $locationId,
            maxQuantity: 100
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $this->locationGateway
            ->method('getDescendantIds')
            ->with($locationId)
            ->willReturn([]);

        // Current stock is 30
        $this->stockRepository
            ->method('getByLocation')
            ->willReturn([
                new Stock('SKU-1', $locationId, null, 30)
            ]);

        // Adding 50 more (30 + 50 = 80 < 100)
        $result = $this->service->canAcceptStock($locationId, 50);

        $this->assertTrue($result->isValid());
    }

    public function test_cannot_accept_stock_exceeds_max_quantity(): void
    {
        $locationId = 'loc-1';
        $settings = LocationStockSettings::createWithCapacity(
            id: 'settings-1',
            locationId: $locationId,
            maxQuantity: 100
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $this->locationGateway
            ->method('getDescendantIds')
            ->willReturn([]);

        // Current stock is 80
        $this->stockRepository
            ->method('getByLocation')
            ->willReturn([
                new Stock('SKU-1', $locationId, null, 80)
            ]);

        // Adding 30 more (80 + 30 = 110 > 100)
        $result = $this->service->canAcceptStock($locationId, 30);

        $this->assertFalse($result->isValid());
        $this->assertEquals('EXCEEDS_MAX_QUANTITY', $result->getErrorCode());
        $this->assertEquals(80, $result->getContext()['current_quantity']);
        $this->assertEquals(30, $result->getContext()['requested_quantity']);
        $this->assertEquals(100, $result->getContext()['max_quantity']);
    }

    public function test_considers_descendant_locations_in_capacity(): void
    {
        $parentId = 'warehouse-1';
        $childId = 'shelf-1';
        
        $settings = LocationStockSettings::createWithCapacity(
            id: 'settings-1',
            locationId: $parentId,
            maxQuantity: 100
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        // Parent has one child
        $this->locationGateway
            ->method('getDescendantIds')
            ->with($parentId)
            ->willReturn([$childId]);

        // Return stock based on location
        $this->stockRepository
            ->method('getByLocation')
            ->willReturnCallback(function ($locId) use ($parentId, $childId) {
                if ($locId === $parentId) {
                    return [new Stock('SKU-1', $parentId, null, 40)];
                }
                if ($locId === $childId) {
                    return [new Stock('SKU-2', $childId, null, 35)];
                }
                return [];
            });

        // Total: 40 + 35 = 75. Adding 30 = 105 > 100
        $result = $this->service->canAcceptStock($parentId, 30);

        $this->assertFalse($result->isValid());
        $this->assertEquals('EXCEEDS_MAX_QUANTITY', $result->getErrorCode());
    }

    public function test_rejects_disallowed_item_type(): void
    {
        $locationId = 'loc-1';
        $settings = new LocationStockSettings(
            id: 'settings-1',
            locationId: $locationId,
            allowedItemTypes: ['hazmat', 'cold_chain']
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $result = $this->service->canAcceptStock($locationId, 10, null, 'regular');

        $this->assertFalse($result->isValid());
        $this->assertEquals('ITEM_TYPE_NOT_ALLOWED', $result->getErrorCode());
        $this->assertEquals('regular', $result->getContext()['item_type']);
        $this->assertEquals(['hazmat', 'cold_chain'], $result->getContext()['allowed_types']);
    }

    public function test_accepts_allowed_item_type(): void
    {
        $locationId = 'loc-1';
        $settings = new LocationStockSettings(
            id: 'settings-1',
            locationId: $locationId,
            allowedItemTypes: ['hazmat', 'cold_chain']
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $this->locationGateway
            ->method('getDescendantIds')
            ->willReturn([]);

        $this->stockRepository
            ->method('getByLocation')
            ->willReturn([]);

        $result = $this->service->canAcceptStock($locationId, 10, null, 'hazmat');

        $this->assertTrue($result->isValid());
    }

    public function test_rejects_mixed_skus_when_not_allowed(): void
    {
        $locationId = 'loc-1';
        $settings = new LocationStockSettings(
            id: 'settings-1',
            locationId: $locationId,
            allowMixedSkus: false
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $this->locationGateway
            ->method('getDescendantIds')
            ->willReturn([]);

        // Location already has SKU-1
        $this->stockRepository
            ->method('getByLocation')
            ->willReturn([
                new Stock('SKU-1', $locationId, null, 50)
            ]);

        // Trying to add different SKU
        $result = $this->service->canAcceptStock($locationId, 10, 'SKU-2');

        $this->assertFalse($result->isValid());
        $this->assertEquals('MIXED_SKUS_NOT_ALLOWED', $result->getErrorCode());
    }

    public function test_accepts_same_sku_when_mixed_skus_not_allowed(): void
    {
        $locationId = 'loc-1';
        $settings = new LocationStockSettings(
            id: 'settings-1',
            locationId: $locationId,
            allowMixedSkus: false
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $this->locationGateway
            ->method('getDescendantIds')
            ->willReturn([]);

        // Location already has SKU-1
        $this->stockRepository
            ->method('getByLocation')
            ->willReturn([
                new Stock('SKU-1', $locationId, null, 50)
            ]);

        // Adding more of the same SKU
        $result = $this->service->canAcceptStock($locationId, 10, 'SKU-1');

        $this->assertTrue($result->isValid());
    }

    public function test_get_total_stock_for_location_tree(): void
    {
        $parentId = 'warehouse-1';
        $childId = 'shelf-1';

        $this->locationGateway
            ->method('getDescendantIds')
            ->with($parentId)
            ->willReturn([$childId]);

        $this->stockRepository
            ->method('getByLocation')
            ->willReturnCallback(function ($locId) use ($parentId, $childId) {
                if ($locId === $parentId) {
                    return [
                        new Stock('SKU-1', $parentId, null, 40),
                        new Stock('SKU-2', $parentId, null, 20)
                    ];
                }
                if ($locId === $childId) {
                    return [new Stock('SKU-3', $childId, null, 35)];
                }
                return [];
            });

        $total = $this->service->getTotalStockForLocationTree($parentId);

        $this->assertEquals(95, $total); // 40 + 20 + 35
    }

    public function test_get_unique_skus(): void
    {
        $locationId = 'loc-1';

        $this->stockRepository
            ->method('getByLocation')
            ->with($locationId)
            ->willReturn([
                new Stock('SKU-1', $locationId, null, 40),
                new Stock('SKU-2', $locationId, null, 20),
                new Stock('SKU-1', $locationId, null, 10), // Duplicate SKU
            ]);

        $skus = $this->service->getUniqueSkus($locationId);

        $this->assertCount(2, $skus);
        $this->assertContains('SKU-1', $skus);
        $this->assertContains('SKU-2', $skus);
    }

    public function test_get_available_capacity_with_settings(): void
    {
        $locationId = 'loc-1';
        $settings = LocationStockSettings::createWithCapacity(
            id: 'settings-1',
            locationId: $locationId,
            maxQuantity: 100
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $this->locationGateway
            ->method('getDescendantIds')
            ->willReturn([]);

        $this->stockRepository
            ->method('getByLocation')
            ->willReturn([
                new Stock('SKU-1', $locationId, null, 60)
            ]);

        $available = $this->service->getAvailableCapacity($locationId);

        $this->assertEquals(40, $available); // 100 - 60
    }

    public function test_get_available_capacity_without_settings_returns_null(): void
    {
        $locationId = 'loc-1';
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn(null);

        $available = $this->service->getAvailableCapacity($locationId);

        $this->assertNull($available);
    }

    public function test_is_location_full(): void
    {
        $locationId = 'loc-1';
        $settings = LocationStockSettings::createWithCapacity(
            id: 'settings-1',
            locationId: $locationId,
            maxQuantity: 100
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $this->locationGateway
            ->method('getDescendantIds')
            ->willReturn([]);

        $this->stockRepository
            ->method('getByLocation')
            ->willReturn([
                new Stock('SKU-1', $locationId, null, 100) // Full
            ]);

        $this->assertTrue($this->service->isLocationFull($locationId));
    }

    public function test_get_capacity_stats(): void
    {
        $locationId = 'loc-1';
        $settings = LocationStockSettings::createWithCapacity(
            id: 'settings-1',
            locationId: $locationId,
            maxQuantity: 100
        );
        
        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($settings);

        $this->locationGateway
            ->method('getDescendantIds')
            ->willReturn([]);

        $this->stockRepository
            ->method('getByLocation')
            ->willReturn([
                new Stock('SKU-1', $locationId, null, 60),
                new Stock('SKU-2', $locationId, null, 15)
            ]);

        $stats = $this->service->getCapacityStats($locationId);

        $this->assertEquals($locationId, $stats['location_id']);
        $this->assertEquals(75, $stats['current_quantity']); // 60 + 15
        $this->assertEquals(100, $stats['max_quantity']);
        $this->assertEquals(25, $stats['available_quantity']); // 100 - 75
        $this->assertEquals(75.0, $stats['usage_percent']);
        $this->assertEquals(2, $stats['unique_skus']);
    }
}
