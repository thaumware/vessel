<?php

declare(strict_types=1);

namespace App\Stock\Tests\Domain;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;

/**
 * Tests de integración de la entidad Movement.
 */
class MovementTest extends StockTestCase
{
    public function test_can_create_movement_with_all_fields(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'ITEM-001',
            locationId: $this->generateUuid(),
            quantity: 100,
            lotId: 'LOT-001',
            referenceType: 'purchase_order',
            referenceId: 'PO-001',
            reason: 'Initial stock',
            performedBy: 'user-1',
            workspaceId: $this->generateUuid(),
            meta: ['source' => 'api']
        );

        $this->assertEquals('ITEM-001', $movement->getItemId());
        $this->assertEquals(100, $movement->getQuantity());
        $this->assertEquals(MovementType::RECEIPT, $movement->getType());
        $this->assertEquals(MovementStatus::PENDING, $movement->getStatus());
        $this->assertEquals('LOT-001', $movement->getLotId());
        $this->assertEquals('purchase_order', $movement->getReferenceType());
        $this->assertEquals('PO-001', $movement->getReferenceId());
    }

    public function test_can_create_incoming_movement(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'ITEM-001',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $this->assertTrue($movement->isInbound());
        $this->assertFalse($movement->isOutbound());
        $this->assertEquals(100, $movement->getEffectiveDelta());
        $this->assertEquals(MovementType::RECEIPT, $movement->getType());
    }

    public function test_can_create_outgoing_movement(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::SHIPMENT,
            itemId: 'ITEM-001',
            locationId: $this->generateUuid(),
            quantity: 50
        );

        $this->assertTrue($movement->isOutbound());
        $this->assertFalse($movement->isInbound());
        $this->assertEquals(-50, $movement->getEffectiveDelta());
        $this->assertEquals(MovementType::SHIPMENT, $movement->getType());
    }

    public function test_can_create_transfer_movement(): void
    {
        $sourceId = $this->generateUuid();
        $destId = $this->generateUuid();

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::TRANSFER_OUT,
            itemId: 'ITEM-001',
            locationId: $sourceId,
            quantity: 25,
            sourceLocationId: $sourceId,
            destinationLocationId: $destId
        );

        $this->assertEquals($sourceId, $movement->getSourceLocationId());
        $this->assertEquals($destId, $movement->getDestinationLocationId());
        $this->assertEquals(MovementType::TRANSFER_OUT, $movement->getType());
        $this->assertEquals(-25, $movement->getEffectiveDelta());
        $this->assertTrue($movement->isTransfer());
    }

    public function test_timestamps_are_set_automatically(): void
    {
        $before = new DateTimeImmutable();

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'ITEM-001',
            locationId: $this->generateUuid(),
            quantity: 10
        );

        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $movement->getCreatedAt());
        $this->assertLessThanOrEqual($after, $movement->getCreatedAt());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'ITEM-001',
            locationId: $this->generateUuid(),
            quantity: 100,
            lotId: 'LOT-001',
            reason: 'Test'
        );

        $array = $movement->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('type_label', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('item_id', $array);
        $this->assertArrayHasKey('location_id', $array);
        $this->assertArrayHasKey('quantity', $array);
        $this->assertArrayHasKey('effective_delta', $array);
        $this->assertArrayHasKey('lot_id', $array);
        $this->assertArrayHasKey('reason', $array);
        $this->assertArrayHasKey('created_at', $array);
    }

    public function test_can_store_meta_data(): void
    {
        $meta = ['source' => 'api', 'batch_id' => '123'];

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'ITEM-001',
            locationId: $this->generateUuid(),
            quantity: 10,
            meta: $meta
        );

        $this->assertEquals($meta, $movement->getMeta());
    }
}
