<?php

namespace App\Stock\Tests\Unit\Domain\Services;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;

/**
 * Tests del servicio de dominio StockMovementService.
 * 
 * Usa el constructor de Movement directamente para crear movimientos de prueba.
 */
class StockMovementServiceTest extends StockTestCase
{
    private StockMovementService $service;
    private MockMovementRepository $movementRepository;
    private MockStockItemRepository $stockItemRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->movementRepository = new MockMovementRepository();
        $this->stockItemRepository = new MockStockItemRepository();
        $this->service = new StockMovementService(
            $this->movementRepository,
            $this->stockItemRepository
        );
    }

    public function test_process_receipt_creates_stock_item(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'SKU-001',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(100, $result->getNewBalance());
        $this->assertEquals(0, $result->getPreviousBalance());
        $this->assertEquals(100, $result->getDelta());
        $this->assertEquals(MovementStatus::COMPLETED, $result->getMovement()->getStatus());
    }

    public function test_process_receipt_adds_to_existing_stock(): void
    {
        $locationId = $this->generateUuid();
        $itemId = 'SKU-001';

        // Pre-existing stock
        $existingItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $locationId,
            locationType: 'warehouse',
            quantity: 50,
            reservedQuantity: 0
        );
        $this->stockItemRepository->save($existingItem);

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: $itemId,
            locationId: $locationId,
            quantity: 30
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(80, $result->getNewBalance());
        $this->assertEquals(50, $result->getPreviousBalance());
        $this->assertEquals(30, $result->getDelta());
    }

    public function test_process_shipment_reduces_stock(): void
    {
        $locationId = $this->generateUuid();
        $itemId = 'SKU-001';

        // Pre-existing stock
        $existingItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $locationId,
            locationType: 'warehouse',
            quantity: 100,
            reservedQuantity: 0
        );
        $this->stockItemRepository->save($existingItem);

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::SHIPMENT,
            itemId: $itemId,
            locationId: $locationId,
            quantity: 30
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(70, $result->getNewBalance());
        $this->assertEquals(-30, $result->getDelta());
    }

    public function test_process_shipment_fails_with_insufficient_stock(): void
    {
        $locationId = $this->generateUuid();
        $itemId = 'SKU-001';

        $existingItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $locationId,
            locationType: 'warehouse',
            quantity: 10,
            reservedQuantity: 0
        );
        $this->stockItemRepository->save($existingItem);

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::SHIPMENT,
            itemId: $itemId,
            locationId: $locationId,
            quantity: 50
        );

        $result = $this->service->process($movement);

        $this->assertFalse($result->isSuccess());
        $this->assertNotEmpty($result->getErrors());
        $this->assertStringContainsString('Stock insuficiente', $result->getErrors()[0]);
    }

    public function test_process_reserve_increases_reserved_quantity(): void
    {
        $locationId = $this->generateUuid();
        $itemId = 'SKU-001';

        $existingItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $locationId,
            locationType: 'warehouse',
            quantity: 100,
            reservedQuantity: 0
        );
        $this->stockItemRepository->save($existingItem);

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RESERVE,
            itemId: $itemId,
            locationId: $locationId,
            quantity: 30
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        // Reserve no cambia quantity, solo reservedQuantity
        $this->assertEquals(100, $result->getNewBalance());
        $this->assertEquals(30, $result->getStockItem()->getReservedQuantity());
        $this->assertEquals(70, $result->getStockItem()->getAvailableQuantity());
    }

    public function test_process_reserve_fails_with_insufficient_available(): void
    {
        $locationId = $this->generateUuid();
        $itemId = 'SKU-001';

        $existingItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $locationId,
            locationType: 'warehouse',
            quantity: 50,
            reservedQuantity: 40 // Only 10 available
        );
        $this->stockItemRepository->save($existingItem);

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RESERVE,
            itemId: $itemId,
            locationId: $locationId,
            quantity: 20
        );

        $result = $this->service->process($movement);

        $this->assertFalse($result->isSuccess());
        $this->assertNotEmpty($result->getErrors());
    }

    public function test_process_release_decreases_reserved_quantity(): void
    {
        $locationId = $this->generateUuid();
        $itemId = 'SKU-001';

        $existingItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $locationId,
            locationType: 'warehouse',
            quantity: 100,
            reservedQuantity: 50
        );
        $this->stockItemRepository->save($existingItem);

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RELEASE,
            itemId: $itemId,
            locationId: $locationId,
            quantity: 30
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(20, $result->getStockItem()->getReservedQuantity());
        $this->assertEquals(80, $result->getStockItem()->getAvailableQuantity());
    }

    public function test_process_release_fails_when_releasing_more_than_reserved(): void
    {
        $locationId = $this->generateUuid();
        $itemId = 'SKU-001';

        $existingItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $locationId,
            locationType: 'warehouse',
            quantity: 100,
            reservedQuantity: 10
        );
        $this->stockItemRepository->save($existingItem);

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RELEASE,
            itemId: $itemId,
            locationId: $locationId,
            quantity: 50
        );

        $result = $this->service->process($movement);

        $this->assertFalse($result->isSuccess());
    }

    public function test_process_adjustment_positive(): void
    {
        $locationId = $this->generateUuid();
        $itemId = 'SKU-001';

        $existingItem = new StockItem(
            id: $this->generateUuid(),
            itemId: $itemId,
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $locationId,
            locationType: 'warehouse',
            quantity: 100,
            reservedQuantity: 0
        );
        $this->stockItemRepository->save($existingItem);

        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::ADJUSTMENT_IN,
            itemId: $itemId,
            locationId: $locationId,
            quantity: 25,
            reason: 'Ajuste de inventario'
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(125, $result->getNewBalance());
    }

    public function test_validate_returns_errors_for_invalid_movement(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::SHIPMENT,
            itemId: 'NONEXISTENT-SKU',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $validation = $this->service->validate($movement);

        $this->assertFalse($validation->isValid());
        $this->assertNotEmpty($validation->getErrors());
    }

    public function test_validate_returns_no_errors_for_valid_receipt(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'NEW-SKU',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $validation = $this->service->validate($movement);

        $this->assertTrue($validation->isValid());
    }

    public function test_process_receipt_with_lot_succeeds(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'SKU-001',
            locationId: $this->generateUuid(),
            quantity: 100,
            lotId: 'LOT-001'
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertTrue($movement->hasLot());
    }

    public function test_movement_is_persisted_after_processing(): void
    {
        $movementId = $this->generateUuid();
        $movement = new Movement(
            id: $movementId,
            type: MovementType::RECEIPT,
            itemId: 'SKU-001',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $result = $this->service->process($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($this->movementRepository->findById($movementId));
    }

    public function test_result_to_array(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'SKU-001',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $result = $this->service->process($movement);
        $array = $result->toArray();

        $this->assertArrayHasKey('success', $array);
        $this->assertArrayHasKey('movement', $array);
        $this->assertArrayHasKey('previous_balance', $array);
        $this->assertArrayHasKey('new_balance', $array);
        $this->assertArrayHasKey('delta', $array);
        $this->assertTrue($array['success']);
    }
}

// Mock implementations for testing
class MockMovementRepository implements \App\Stock\Domain\Interfaces\MovementRepositoryInterface
{
    private array $movements = [];

    public function save(Movement $movement): Movement
    {
        $this->movements[$movement->getId()] = $movement;
        return $movement;
    }

    public function findById(string $id): ?Movement
    {
        return $this->movements[$id] ?? null;
    }

    public function findByMovementId(string $movementId): ?Movement
    {
        return $this->findById($movementId);
    }

    public function findBySku(string $sku): array { return []; }
    public function findByLocationFrom(string $locationId): array { return []; }
    public function findByLocationTo(string $locationId): array { return []; }
    public function findByType(\App\Stock\Domain\ValueObjects\MovementType $type): array { return []; }
    public function findByStatus(\App\Stock\Domain\ValueObjects\MovementStatus $status): array { return []; }
    public function findByReference(string $reference): array { return []; }
    public function findByLotId(string $lotId): array { return []; }
    public function findByDateRange(\DateTimeInterface $from, \DateTimeInterface $to): array { return []; }
    public function delete(string $id): bool { return true; }
    public function all(): array { return array_values($this->movements); }
    public function search(\App\Stock\Domain\ValueObjects\MovementSearchCriteria $criteria): array { return []; }
    public function count(\App\Stock\Domain\ValueObjects\MovementSearchCriteria $criteria): int { return 0; }
}

class MockStockItemRepository implements \App\Stock\Domain\Interfaces\StockItemRepositoryInterface
{
    private array $items = [];

    public function save(StockItem $item): StockItem
    {
        $this->items[$item->getId()] = $item;
        return $item;
    }

    public function findById(string $id): ?StockItem
    {
        return $this->items[$id] ?? null;
    }

    public function findByItemId(string $itemId): array
    {
        return array_values(array_filter($this->items, fn(StockItem $i) => $i->getItemId() === $itemId));
    }

    public function findByItemAndLocation(string $itemId, string $locationId): ?StockItem
    {
        foreach ($this->items as $item) {
            if ($item->getItemId() === $itemId && $item->getLocationId() === $locationId) {
                return $item;
            }
        }
        return null;
    }

    public function findByLocation(string $locationId): array
    {
        return array_values(array_filter($this->items, fn(StockItem $i) => $i->getLocationId() === $locationId));
    }

    public function findByCatalogItemId(string $catalogItemId, string $catalogOrigin): array { return []; }

    public function search(array $filters = [], int $limit = 50, int $offset = 0): array { return []; }

    public function update(StockItem $item): StockItem { return $this->save($item); }

    public function delete(string $id): void { unset($this->items[$id]); }

    public function adjustQuantity(string $itemId, string $locationId, int $delta): StockItem
    {
        $found = $this->findByItemAndLocation($itemId, $locationId);
        if (!$found) {
            throw new \RuntimeException('StockItem not found');
        }
        $updated = $found->adjustQuantity($delta);
        $this->save($updated);
        return $updated;
    }

    public function reserve(string $id, int $quantity): StockItem
    {
        $found = $this->findById($id);
        if (!$found) {
            throw new \RuntimeException('StockItem not found');
        }
        $updated = $found->reserve($quantity);
        $this->save($updated);
        return $updated;
    }

    public function release(string $id, int $quantity): StockItem
    {
        $found = $this->findById($id);
        if (!$found) {
            throw new \RuntimeException('StockItem not found');
        }
        $updated = $found->release($quantity);
        $this->save($updated);
        return $updated;
    }

    public function findWithCatalogItems(array $ids): array { return []; }
}
