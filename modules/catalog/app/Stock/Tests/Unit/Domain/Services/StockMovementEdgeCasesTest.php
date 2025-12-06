<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\Services;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Infrastructure\Out\InMemory\InMemoryStockItemRepository;
use App\Stock\Infrastructure\Out\InMemory\InMemoryMovementRepository;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;

/**
 * Tests de casos limite para StockMovementService.
 */
class StockMovementEdgeCasesTest extends StockTestCase
{
    private StockMovementService $service;
    private InMemoryStockItemRepository $stockItemRepo;
    private InMemoryMovementRepository $movementRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockItemRepo = new InMemoryStockItemRepository();
        $this->movementRepo = new InMemoryMovementRepository();
        $this->service = new StockMovementService(
            $this->movementRepo,
            $this->stockItemRepo
        );
    }

    private function createStockItem(
        string $id,
        string $itemId,
        string $locationId,
        float $quantity,
        float $reservedQuantity = 0
    ): StockItem {
        return new StockItem(
            id: $id,
            itemId: $itemId,
            catalogItemId: 'cat-' . $id,
            catalogOrigin: 'internal',
            locationId: $locationId,
            quantity: $quantity,
            reservedQuantity: $reservedQuantity
        );
    }

    // === Casos limite de cantidad ===

    public function test_shipment_exactly_available_quantity_succeeds(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::SHIPMENT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 100,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        $updatedStock = $this->stockItemRepo->findById('item-1');
        $this->assertEquals(0.0, $updatedStock->getQuantity());
    }

    public function test_shipment_more_than_available_fails(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 50, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::SHIPMENT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 51, // Una mas de lo disponible
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertFalse($result->isSuccess());
        $this->assertNotEmpty($result->getErrors());
    }

    public function test_shipment_with_reserved_stock_uses_only_available(): void
    {
        // 100 totales, 30 reservadas = 70 disponibles
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 30);
        $this->stockItemRepo->save($stockItem);

        // Intentar enviar 71 (mas que las 70 disponibles)
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::SHIPMENT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 71,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertFalse($result->isSuccess());
    }

    public function test_receipt_creates_new_stock_item_if_not_exists(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'NEW-PROD',
            locationId: 'loc-001',
            quantity: 50,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        // Verificar que se creo el stock
        $item = $this->stockItemRepo->findByItemAndLocation('NEW-PROD', 'loc-001');
        $this->assertNotNull($item);
        $this->assertEquals(50.0, $item->getQuantity());
    }

    public function test_receipt_adds_to_existing_stock(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 50,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        $updatedStock = $this->stockItemRepo->findById('item-1');
        $this->assertEquals(150.0, $updatedStock->getQuantity());
    }

    // === Casos limite de reservas ===

    public function test_reserve_exactly_available_quantity(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RESERVE,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 100,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        $updatedStock = $this->stockItemRepo->findById('item-1');
        $this->assertEquals(100.0, $updatedStock->getReservedQuantity());
        $this->assertEquals(0.0, $updatedStock->getAvailableQuantity());
    }

    public function test_reserve_more_than_available_fails(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 50, 20);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RESERVE,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 31, // Mas que las 30 disponibles
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertFalse($result->isSuccess());
    }

    public function test_release_more_than_reserved_fails(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 30);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RELEASE,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 31, // Mas que las 30 reservadas
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertFalse($result->isSuccess());
    }

    public function test_release_exactly_reserved_quantity(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 30);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RELEASE,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 30,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        $updatedStock = $this->stockItemRepo->findById('item-1');
        $this->assertEquals(0.0, $updatedStock->getReservedQuantity());
        $this->assertEquals(100.0, $updatedStock->getAvailableQuantity());
    }

    // === Casos limite de ajustes ===

    public function test_negative_adjustment_to_zero(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 50, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::ADJUSTMENT_OUT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 50, // Ajustar a cero
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        $updatedStock = $this->stockItemRepo->findById('item-1');
        $this->assertEquals(0.0, $updatedStock->getQuantity());
    }

    public function test_negative_adjustment_below_zero_fails(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 30, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::ADJUSTMENT_OUT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 31, // Mas que lo disponible
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertFalse($result->isSuccess());
    }

    public function test_negative_adjustment_cannot_go_below_reserved(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 40);
        $this->stockItemRepo->save($stockItem);

        // Intentar ajustar -70 (dejaria 30, menos que las 40 reservadas)
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::ADJUSTMENT_OUT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 70,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        // Debe fallar porque no puede quedar menos stock que el reservado
        $this->assertFalse($result->isSuccess());
    }

    // === Casos limite de balance ===

    public function test_balance_after_is_calculated_correctly(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::SHIPMENT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 30,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(100.0, $result->getPreviousBalance());
        $this->assertEquals(70.0, $result->getNewBalance());
        
        // Verificar que el stock item se actualizó correctamente
        $updatedStock = $this->stockItemRepo->findById('item-1');
        $this->assertEquals(70.0, $updatedStock->getQuantity());
    }

    // === Casos con lotes ===

    public function test_movement_with_lot_number_is_tracked(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 50,
            status: MovementStatus::PENDING,
            lotId: 'LOT-2024-001'
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        $savedMovement = $this->movementRepo->findById('mov-1');
        $this->assertEquals('LOT-2024-001', $savedMovement->getLotId());
        $this->assertTrue($savedMovement->hasLot());
    }

    // === Validacion de movimientos ===

    public function test_validate_returns_errors_for_insufficient_stock(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 10, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::SHIPMENT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 100,
            status: MovementStatus::PENDING
        );

        $validation = $this->service->validate($movement);

        $this->assertFalse($validation->isValid());
        $this->assertNotEmpty($validation->getErrors());
    }

    public function test_validate_passes_for_valid_movement(): void
    {
        $stockItem = $this->createStockItem('item-1', 'PROD-001', 'loc-001', 100, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::SHIPMENT,
            itemId: 'PROD-001',
            locationId: 'loc-001',
            quantity: 50,
            status: MovementStatus::PENDING
        );

        $validation = $this->service->validate($movement);

        $this->assertTrue($validation->isValid());
    }

    // === Cantidades decimales (float) ===

    public function test_decimal_quantity_receipt(): void
    {
        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::RECEIPT,
            itemId: 'BULK-001',
            locationId: 'loc-001',
            quantity: 2.5, // 2.5 kg
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        $item = $this->stockItemRepo->findByItemAndLocation('BULK-001', 'loc-001');
        $this->assertNotNull($item);
        $this->assertEquals(2.5, $item->getQuantity());
    }

    public function test_decimal_quantity_partial_shipment(): void
    {
        $stockItem = $this->createStockItem('item-1', 'BULK-001', 'loc-001', 10.75, 0);
        $this->stockItemRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-1',
            type: MovementType::SHIPMENT,
            itemId: 'BULK-001',
            locationId: 'loc-001',
            quantity: 3.25,
            status: MovementStatus::PENDING
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        
        $updatedStock = $this->stockItemRepo->findById('item-1');
        $this->assertEquals(7.5, $updatedStock->getQuantity());
    }
}
