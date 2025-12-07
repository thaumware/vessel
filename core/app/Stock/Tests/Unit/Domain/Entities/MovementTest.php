<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\Entities;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\ValueObjects\MovementType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Tests de la entidad Movement.
 * 
 * Estos tests prueban la entidad pura de dominio usando el constructor.
 * Para tests de factory methods, ver MovementFactoryTest.
 */
class MovementTest extends TestCase
{
    public function test_receipt_adds_stock(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 100
        );

        $this->assertEquals(MovementType::RECEIPT, $movement->getType());
        $this->assertEquals(100, $movement->getQuantity());
        $this->assertEquals(100, $movement->getEffectiveDelta());
        $this->assertTrue($movement->isInbound());
        $this->assertFalse($movement->isOutbound());
        $this->assertTrue($movement->affectsQuantity());
    }

    public function test_shipment_removes_stock(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::SHIPMENT,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 50
        );

        $this->assertEquals(MovementType::SHIPMENT, $movement->getType());
        $this->assertEquals(50, $movement->getQuantity());
        $this->assertEquals(-50, $movement->getEffectiveDelta());
        $this->assertTrue($movement->isOutbound());
        $this->assertFalse($movement->isInbound());
    }

    public function test_reserve_affects_reservation(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RESERVE,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 10,
            referenceId: 'order-123'
        );

        $this->assertEquals(MovementType::RESERVE, $movement->getType());
        $this->assertEquals(0, $movement->getEffectiveDelta());
        $this->assertEquals(10, $movement->getReservationDelta());
        $this->assertTrue($movement->affectsReservation());
        $this->assertFalse($movement->affectsQuantity());
    }

    public function test_release_releases_reservation(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RELEASE,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 10
        );

        $this->assertEquals(MovementType::RELEASE, $movement->getType());
        $this->assertEquals(-10, $movement->getReservationDelta());
    }

    public function test_adjustment_in(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::ADJUSTMENT_IN,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 25,
            reason: 'Inventario físico'
        );

        $this->assertEquals(MovementType::ADJUSTMENT_IN, $movement->getType());
        $this->assertEquals(25, $movement->getEffectiveDelta());
        $this->assertEquals('Inventario físico', $movement->getReason());
    }

    public function test_adjustment_out(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::ADJUSTMENT_OUT,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 15,
            reason: 'Merma detectada'
        );

        $this->assertEquals(MovementType::ADJUSTMENT_OUT, $movement->getType());
        $this->assertEquals(-15, $movement->getEffectiveDelta());
    }

    public function test_transfer_out(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::TRANSFER_OUT,
            itemId: 'PROD-001',
            locationId: 'warehouse-1',
            quantity: 30,
            sourceLocationId: 'warehouse-1',
            destinationLocationId: 'store-1'
        );

        $this->assertEquals(MovementType::TRANSFER_OUT, $movement->getType());
        $this->assertEquals('warehouse-1', $movement->getSourceLocationId());
        $this->assertEquals('store-1', $movement->getDestinationLocationId());
        $this->assertEquals(-30, $movement->getEffectiveDelta());
        $this->assertTrue($movement->isTransfer());
    }

    public function test_transfer_in(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::TRANSFER_IN,
            itemId: 'PROD-001',
            locationId: 'store-1',
            quantity: 30,
            sourceLocationId: 'warehouse-1',
            destinationLocationId: 'store-1'
        );

        $this->assertEquals(MovementType::TRANSFER_IN, $movement->getType());
        $this->assertEquals(30, $movement->getEffectiveDelta());
        $this->assertTrue($movement->isTransfer());
    }

    public function test_count_does_not_affect_quantity(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::COUNT,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 100
        );

        $this->assertEquals(MovementType::COUNT, $movement->getType());
        $this->assertEquals(0, $movement->getEffectiveDelta());
        $this->assertFalse($movement->affectsQuantity());
    }

    public function test_receipt_with_lot(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 100,
            lotId: 'LOT-2024-001'
        );

        $this->assertTrue($movement->hasLot());
        $this->assertEquals('LOT-2024-001', $movement->getLotId());
    }

    public function test_receipt_with_tracked_unit(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 1,
            trackedUnitId: 'UNIT-001'
        );

        $this->assertTrue($movement->hasTrackedUnit());
        $this->assertEquals('UNIT-001', $movement->getTrackedUnitId());
        $this->assertTrue($movement->hasTracking());
    }

    public function test_status_transitions(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'SKU-1',
            locationId: 'loc-1',
            quantity: 100
        );

        $this->assertEquals(MovementStatus::PENDING, $movement->getStatus());
        $this->assertTrue($movement->canProcess());
        $this->assertTrue($movement->canCancel());

        $completed = $movement->markAsCompleted();
        $this->assertEquals(MovementStatus::COMPLETED, $completed->getStatus());
        $this->assertNotNull($completed->getProcessedAt());
        $this->assertFalse($completed->canProcess());

        // Original inmutable
        $this->assertEquals(MovementStatus::PENDING, $movement->getStatus());

        $cancelled = $movement->markAsCancelled();
        $this->assertEquals(MovementStatus::CANCELLED, $cancelled->getStatus());
    }

    public function test_source_tracking(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'SKU-1',
            locationId: 'loc-1',
            quantity: 100,
            sourceType: 'supplier',
            sourceId: 'SUP-001'
        );

        $this->assertTrue($movement->hasSource());
        $this->assertEquals('supplier', $movement->getSourceType());
        $this->assertEquals('SUP-001', $movement->getSourceId());
    }

    public function test_reference_tracking(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'SKU-1',
            locationId: 'loc-1',
            quantity: 100,
            referenceType: 'purchase_order',
            referenceId: 'PO-001'
        );

        $this->assertTrue($movement->hasReference());
        $this->assertEquals('purchase_order', $movement->getReferenceType());
        $this->assertEquals('PO-001', $movement->getReferenceId());
    }

    public function test_to_array(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'PROD-001',
            locationId: 'loc-1',
            quantity: 100,
            lotId: 'LOT-001',
            reason: 'Test receipt'
        );

        $array = $movement->toArray();

        $this->assertEquals('mov-1', $array['id']);
        $this->assertEquals('receipt', $array['type']);
        $this->assertEquals('Recepción', $array['type_label']);
        $this->assertEquals('pending', $array['status']);
        $this->assertEquals('PROD-001', $array['item_id']);
        $this->assertEquals('loc-1', $array['location_id']);
        $this->assertEquals(100, $array['quantity']);
        $this->assertEquals(100, $array['effective_delta']);
        $this->assertEquals('LOT-001', $array['lot_id']);
        $this->assertEquals('Test receipt', $array['reason']);
    }

}
