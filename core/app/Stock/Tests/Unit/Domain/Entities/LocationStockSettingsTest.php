<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\Entities;

use App\Stock\Domain\Entities\LocationStockSettings;
use PHPUnit\Framework\TestCase;

class LocationStockSettingsTest extends TestCase
{
    public function test_create_default_settings(): void
    {
        $settings = LocationStockSettings::createDefault('id-1', 'loc-1');

        $this->assertEquals('id-1', $settings->getId());
        $this->assertEquals('loc-1', $settings->getLocationId());
        $this->assertNull($settings->getMaxQuantity());
        $this->assertNull($settings->getMaxWeight());
        $this->assertNull($settings->getMaxVolume());
        $this->assertNull($settings->getAllowedItemTypes());
        $this->assertTrue($settings->allowsMixedLots());
        $this->assertTrue($settings->allowsMixedSkus());
        $this->assertFalse($settings->isFifoEnforced());
        $this->assertTrue($settings->isActive());
    }

    public function test_create_with_capacity(): void
    {
        $settings = LocationStockSettings::createWithCapacity('id-1', 'loc-1', 100);

        $this->assertEquals(100, $settings->getMaxQuantity());
        $this->assertTrue($settings->hasCapacityLimit());
    }

    public function test_create_full_settings(): void
    {
        $settings = new LocationStockSettings(
            id: 'id-1',
            locationId: 'loc-1',
            maxQuantity: 100,
            maxWeight: 500.5,
            maxVolume: 10.0,
            allowedItemTypes: ['hazmat', 'cold_chain'],
            allowMixedLots: false,
            allowMixedSkus: false,
            fifoEnforced: true,
            isActive: true,
            workspaceId: 'ws-1',
            meta: ['note' => 'test']
        );

        $this->assertEquals('loc-1', $settings->getLocationId());
        $this->assertEquals(100, $settings->getMaxQuantity());
        $this->assertEquals(500.5, $settings->getMaxWeight());
        $this->assertEquals(10.0, $settings->getMaxVolume());
        $this->assertEquals(['hazmat', 'cold_chain'], $settings->getAllowedItemTypes());
        $this->assertFalse($settings->allowsMixedLots());
        $this->assertFalse($settings->allowsMixedSkus());
        $this->assertTrue($settings->isFifoEnforced());
        $this->assertEquals('ws-1', $settings->getWorkspaceId());
        $this->assertEquals(['note' => 'test'], $settings->getMeta());
    }

    public function test_has_capacity_limit(): void
    {
        $noLimit = LocationStockSettings::createDefault('id-1', 'loc-1');
        $this->assertFalse($noLimit->hasCapacityLimit());

        $withQuantity = new LocationStockSettings(
            id: 'id-2',
            locationId: 'loc-2',
            maxQuantity: 100
        );
        $this->assertTrue($withQuantity->hasCapacityLimit());

        $withWeight = new LocationStockSettings(
            id: 'id-3',
            locationId: 'loc-3',
            maxWeight: 500.0
        );
        $this->assertTrue($withWeight->hasCapacityLimit());

        $withVolume = new LocationStockSettings(
            id: 'id-4',
            locationId: 'loc-4',
            maxVolume: 10.0
        );
        $this->assertTrue($withVolume->hasCapacityLimit());
    }

    public function test_is_item_type_allowed(): void
    {
        // Sin restricciones - todo permitido
        $noRestriction = LocationStockSettings::createDefault('id-1', 'loc-1');
        $this->assertTrue($noRestriction->isItemTypeAllowed('anything'));

        // Con restricciones
        $restricted = new LocationStockSettings(
            id: 'id-2',
            locationId: 'loc-2',
            allowedItemTypes: ['hazmat', 'cold_chain']
        );
        $this->assertTrue($restricted->isItemTypeAllowed('hazmat'));
        $this->assertTrue($restricted->isItemTypeAllowed('cold_chain'));
        $this->assertFalse($restricted->isItemTypeAllowed('regular'));
        $this->assertFalse($restricted->isItemTypeAllowed('bulk'));
    }

    public function test_get_remaining_capacity(): void
    {
        $settings = LocationStockSettings::createWithCapacity('id-1', 'loc-1', 100);

        $this->assertEquals(60, $settings->getRemainingCapacity(40));
        $this->assertEquals(0, $settings->getRemainingCapacity(100));
        $this->assertEquals(0, $settings->getRemainingCapacity(150)); // Over capacity returns 0
    }

    public function test_get_remaining_capacity_without_limit(): void
    {
        $settings = LocationStockSettings::createDefault('id-1', 'loc-1');

        $this->assertNull($settings->getRemainingCapacity(1000)); // No limit
    }

    public function test_can_accept_quantity(): void
    {
        $settings = LocationStockSettings::createWithCapacity('id-1', 'loc-1', 100);

        $this->assertTrue($settings->canAcceptQuantity(40, 50)); // 90 < 100
        $this->assertTrue($settings->canAcceptQuantity(40, 60)); // 100 = 100
        $this->assertFalse($settings->canAcceptQuantity(40, 70)); // 110 > 100
    }

    public function test_can_accept_quantity_without_limit(): void
    {
        $settings = LocationStockSettings::createDefault('id-1', 'loc-1');

        $this->assertTrue($settings->canAcceptQuantity(1000, 1000)); // No limit
    }

    public function test_with_max_quantity(): void
    {
        $original = LocationStockSettings::createDefault('id-1', 'loc-1');
        $updated = $original->withMaxQuantity(100);

        $this->assertNull($original->getMaxQuantity()); // Inmutable
        $this->assertEquals(100, $updated->getMaxQuantity());
        $this->assertEquals($original->getId(), $updated->getId());
    }

    public function test_with_max_weight(): void
    {
        $original = LocationStockSettings::createDefault('id-1', 'loc-1');
        $updated = $original->withMaxWeight(500.5);

        $this->assertNull($original->getMaxWeight()); // Inmutable
        $this->assertEquals(500.5, $updated->getMaxWeight());
    }

    public function test_activate_deactivate(): void
    {
        $settings = LocationStockSettings::createDefault('id-1', 'loc-1');

        $deactivated = $settings->deactivate();
        $this->assertTrue($settings->isActive()); // Original inmutable
        $this->assertFalse($deactivated->isActive());

        $reactivated = $deactivated->activate();
        $this->assertFalse($deactivated->isActive()); // Inmutable
        $this->assertTrue($reactivated->isActive());
    }

    public function test_to_array(): void
    {
        $settings = new LocationStockSettings(
            id: 'id-1',
            locationId: 'loc-1',
            maxQuantity: 100,
            maxWeight: 500.5,
            maxVolume: 10.0,
            allowedItemTypes: ['hazmat'],
            allowMixedLots: false,
            allowMixedSkus: true,
            fifoEnforced: true,
            isActive: true,
            workspaceId: 'ws-1',
            meta: ['note' => 'test']
        );

        $array = $settings->toArray();

        $this->assertEquals('id-1', $array['id']);
        $this->assertEquals('loc-1', $array['location_id']);
        $this->assertEquals(100, $array['max_quantity']);
        $this->assertEquals(500.5, $array['max_weight']);
        $this->assertEquals(10.0, $array['max_volume']);
        $this->assertEquals(['hazmat'], $array['allowed_item_types']);
        $this->assertFalse($array['allow_mixed_lots']);
        $this->assertTrue($array['allow_mixed_skus']);
        $this->assertTrue($array['fifo_enforced']);
        $this->assertTrue($array['is_active']);
        $this->assertEquals('ws-1', $array['workspace_id']);
        $this->assertEquals(['note' => 'test'], $array['meta']);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    public function test_timestamps_are_set(): void
    {
        $settings = LocationStockSettings::createDefault('id-1', 'loc-1');

        $this->assertNotNull($settings->getCreatedAt());
        $this->assertNotNull($settings->getUpdatedAt());
    }
}
