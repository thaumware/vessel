<?php

declare(strict_types=1);

namespace App\Stock\Tests\Application\Factories;

use App\Stock\Application\Factories\MovementFactory;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Tests\Support\TestIdGenerator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Tests de la factory de movimientos.
 * 
 * Estos tests verifican que la factory crea movimientos correctamente
 * con los referenceTypes y defaults apropiados.
 */
class MovementFactoryTest extends TestCase
{
    private MovementFactory $factory;
    private TestIdGenerator $idGenerator;

    protected function setUp(): void
    {
        $this->idGenerator = new TestIdGenerator();
        $this->factory = new MovementFactory($this->idGenerator);
    }

    public function test_create_receipt(): void
    {
        $this->idGenerator->setNextId('receipt-001');

        $movement = $this->factory->createReceipt(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 100,
            lotId: 'LOT-001',
            sourceType: 'supplier',
            sourceId: 'SUP-001',
            referenceId: 'PO-001',
            reason: 'Initial stock'
        );

        $this->assertEquals('receipt-001', $movement->getId());
        $this->assertEquals(MovementType::RECEIPT, $movement->getType());
        $this->assertEquals('ITEM-001', $movement->getItemId());
        $this->assertEquals('loc-1', $movement->getLocationId());
        $this->assertEquals(100, $movement->getQuantity());
        $this->assertEquals('LOT-001', $movement->getLotId());
        $this->assertEquals('supplier', $movement->getSourceType());
        $this->assertEquals('SUP-001', $movement->getSourceId());
        $this->assertEquals('purchase_order', $movement->getReferenceType());
        $this->assertEquals('PO-001', $movement->getReferenceId());
        $this->assertEquals('Initial stock', $movement->getReason());
        $this->assertEquals(MovementStatus::PENDING, $movement->getStatus());
    }

    public function test_create_shipment(): void
    {
        $this->idGenerator->setNextId('ship-001');

        $movement = $this->factory->createShipment(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 50,
            lotId: 'LOT-001',
            referenceId: 'SO-001'
        );

        $this->assertEquals('ship-001', $movement->getId());
        $this->assertEquals(MovementType::SHIPMENT, $movement->getType());
        $this->assertEquals(50, $movement->getQuantity());
        $this->assertEquals(-50, $movement->getEffectiveDelta());
        $this->assertEquals('sales_order', $movement->getReferenceType());
        $this->assertEquals('SO-001', $movement->getReferenceId());
    }

    public function test_create_reservation(): void
    {
        $this->idGenerator->setNextId('res-001');

        $movement = $this->factory->createReservation(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 10,
            referenceId: 'ORDER-001'
        );

        $this->assertEquals(MovementType::RESERVE, $movement->getType());
        $this->assertEquals(10, $movement->getQuantity());
        $this->assertEquals(0, $movement->getEffectiveDelta());
        $this->assertEquals(10, $movement->getReservationDelta());
        $this->assertEquals('sales_order', $movement->getReferenceType());
    }

    public function test_create_release(): void
    {
        $movement = $this->factory->createRelease(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 10,
            referenceId: 'ORDER-001'
        );

        $this->assertEquals(MovementType::RELEASE, $movement->getType());
        $this->assertEquals(-10, $movement->getReservationDelta());
        $this->assertEquals('sales_order', $movement->getReferenceType());
    }

    public function test_create_adjustment_positive(): void
    {
        $movement = $this->factory->createAdjustment(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            delta: 25,
            reason: 'Found extra stock'
        );

        $this->assertEquals(MovementType::ADJUSTMENT_IN, $movement->getType());
        $this->assertEquals(25, $movement->getQuantity());
        $this->assertEquals(25, $movement->getEffectiveDelta());
        $this->assertEquals('inventory_adjustment', $movement->getReferenceType());
        $this->assertEquals('Found extra stock', $movement->getReason());
    }

    public function test_create_adjustment_negative(): void
    {
        $movement = $this->factory->createAdjustment(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            delta: -15,
            reason: 'Shrinkage detected'
        );

        $this->assertEquals(MovementType::ADJUSTMENT_OUT, $movement->getType());
        $this->assertEquals(15, $movement->getQuantity()); // Stored as positive
        $this->assertEquals(-15, $movement->getEffectiveDelta());
        $this->assertEquals('Shrinkage detected', $movement->getReason());
    }

    public function test_create_transfer_out(): void
    {
        $movement = $this->factory->createTransferOut(
            itemId: 'ITEM-001',
            sourceLocationId: 'warehouse-1',
            destinationLocationId: 'store-1',
            quantity: 30,
            lotId: 'LOT-001',
            transferId: 'TRF-001'
        );

        $this->assertEquals(MovementType::TRANSFER_OUT, $movement->getType());
        $this->assertEquals('warehouse-1', $movement->getLocationId());
        $this->assertEquals('warehouse-1', $movement->getSourceLocationId());
        $this->assertEquals('store-1', $movement->getDestinationLocationId());
        $this->assertEquals(-30, $movement->getEffectiveDelta());
        $this->assertEquals('transfer', $movement->getReferenceType());
        $this->assertEquals('TRF-001', $movement->getReferenceId());
    }

    public function test_create_transfer_in(): void
    {
        $movement = $this->factory->createTransferIn(
            itemId: 'ITEM-001',
            sourceLocationId: 'warehouse-1',
            destinationLocationId: 'store-1',
            quantity: 30,
            transferId: 'TRF-001'
        );

        $this->assertEquals(MovementType::TRANSFER_IN, $movement->getType());
        $this->assertEquals('store-1', $movement->getLocationId());
        $this->assertEquals(30, $movement->getEffectiveDelta());
        $this->assertEquals('transfer', $movement->getReferenceType());
    }

    public function test_create_count(): void
    {
        $movement = $this->factory->createCount(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            countedQuantity: 100,
            lotId: 'LOT-001',
            reason: 'Monthly inventory'
        );

        $this->assertEquals(MovementType::COUNT, $movement->getType());
        $this->assertEquals(100, $movement->getQuantity());
        $this->assertEquals(0, $movement->getEffectiveDelta());
        $this->assertEquals('inventory_count', $movement->getReferenceType());
        $this->assertEquals('Monthly inventory', $movement->getReason());
    }

    public function test_create_expiration(): void
    {
        $movement = $this->factory->createExpiration(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 50,
            lotId: 'LOT-001',
            reason: 'Expired milk'
        );

        $this->assertEquals(MovementType::EXPIRATION, $movement->getType());
        $this->assertEquals(-50, $movement->getEffectiveDelta());
        $this->assertEquals('expiration', $movement->getReferenceType());
        $this->assertEquals('LOT-001', $movement->getLotId());
        $this->assertEquals('Expired milk', $movement->getReason());
    }

    public function test_create_expiration_has_default_reason(): void
    {
        $movement = $this->factory->createExpiration(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 50,
            lotId: 'LOT-001'
        );

        $this->assertEquals('Stock vencido', $movement->getReason());
    }

    public function test_create_installation(): void
    {
        $movement = $this->factory->createInstallation(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 1,
            workOrderId: 'WO-001',
            reason: 'Customer installation'
        );

        $this->assertEquals(MovementType::INSTALLATION, $movement->getType());
        $this->assertEquals(-1, $movement->getEffectiveDelta());
        $this->assertEquals('work_order', $movement->getReferenceType());
        $this->assertEquals('WO-001', $movement->getReferenceId());
        $this->assertEquals('Customer installation', $movement->getReason());
    }

    public function test_create_installation_has_default_reason(): void
    {
        $movement = $this->factory->createInstallation(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 1
        );

        $this->assertEquals('Instalación en cliente', $movement->getReason());
    }

    public function test_create_customer_return(): void
    {
        $movement = $this->factory->createCustomerReturn(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 1,
            returnOrderId: 'RMA-001',
            reason: 'Defective unit'
        );

        $this->assertEquals(MovementType::RETURN, $movement->getType());
        $this->assertEquals(1, $movement->getEffectiveDelta());
        $this->assertEquals('return_order', $movement->getReferenceType());
        $this->assertEquals('RMA-001', $movement->getReferenceId());
        $this->assertEquals('Defective unit', $movement->getReason());
    }

    public function test_create_customer_return_has_default_reason(): void
    {
        $movement = $this->factory->createCustomerReturn(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 1
        );

        $this->assertEquals('Devolución de cliente', $movement->getReason());
    }

    public function test_create_damage(): void
    {
        $movement = $this->factory->createDamage(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 5,
            reason: 'Water damage'
        );

        $this->assertEquals(MovementType::DAMAGE, $movement->getType());
        $this->assertEquals(-5, $movement->getEffectiveDelta());
        $this->assertEquals('damage_report', $movement->getReferenceType());
        $this->assertEquals('Water damage', $movement->getReason());
    }

    public function test_create_damage_has_default_reason(): void
    {
        $movement = $this->factory->createDamage(
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 5
        );

        $this->assertEquals('Producto dañado', $movement->getReason());
    }

    public function test_create_generic(): void
    {
        $movement = $this->factory->create(
            type: MovementType::RECEIPT,
            itemId: 'ITEM-001',
            locationId: 'loc-1',
            quantity: 100,
            lotId: 'LOT-001',
            sourceType: 'production',
            sourceId: 'BATCH-001',
            referenceType: 'custom_type',
            referenceId: 'REF-001',
            reason: 'Custom movement'
        );

        $this->assertEquals(MovementType::RECEIPT, $movement->getType());
        $this->assertEquals('custom_type', $movement->getReferenceType());
        $this->assertEquals('REF-001', $movement->getReferenceId());
        $this->assertEquals('production', $movement->getSourceType());
        $this->assertEquals('BATCH-001', $movement->getSourceId());
        $this->assertEquals('Custom movement', $movement->getReason());
    }

    public function test_id_generator_increments(): void
    {
        $this->idGenerator->setPrefix('mov-');

        $m1 = $this->factory->createReceipt('ITEM-1', 'loc-1', 10);
        $m2 = $this->factory->createReceipt('ITEM-2', 'loc-1', 20);
        $m3 = $this->factory->createReceipt('ITEM-3', 'loc-1', 30);

        $this->assertEquals('mov-1', $m1->getId());
        $this->assertEquals('mov-2', $m2->getId());
        $this->assertEquals('mov-3', $m3->getId());
    }
}
