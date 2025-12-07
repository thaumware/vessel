<?php

namespace App\Stock\Tests\Infrastructure;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Infrastructure\Out\InMemory\InMemoryMovementRepository;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;

class InMemoryMovementRepositoryTest extends StockTestCase
{
    private InMemoryMovementRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryMovementRepository();
    }

    public function test_save_and_find_by_id(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'SKU-001',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $this->repository->save($movement);
        $found = $this->repository->findById($movement->getId());

        $this->assertNotNull($found);
        $this->assertEquals($movement->getId(), $found->getId());
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->repository->findById($this->generateUuid());
        $this->assertNull($found);
    }

    public function test_find_by_movement_id(): void
    {
        $id = $this->generateUuid();
        $movement = new Movement(
            id: $id,
            type: MovementType::RECEIPT,
            itemId: 'SKU-001',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $this->repository->save($movement);
        $found = $this->repository->findByMovementId($id);

        $this->assertNotNull($found);
        $this->assertEquals($id, $found->getId());
    }

    public function test_find_by_movement_id_returns_null_when_not_found(): void
    {
        $found = $this->repository->findByMovementId('NONEXISTENT');
        $this->assertNull($found);
    }

    public function test_find_by_sku(): void
    {
        $sku = 'COMMON-SKU';

        $this->repository->save($this->createMovement(['sku' => $sku]));
        $this->repository->save($this->createMovement(['sku' => $sku]));
        $this->repository->save($this->createMovement(['sku' => 'OTHER-SKU']));

        $found = $this->repository->findBySku($sku);

        $this->assertCount(2, $found);
    }

    public function test_find_by_location_from(): void
    {
        $locationId = $this->generateUuid();

        // SHIPMENT removes stock from locationId
        $this->repository->save($this->createMovement([
            'type' => MovementType::SHIPMENT,
            'locationId' => $locationId
        ]));
        $this->repository->save($this->createMovement([
            'type' => MovementType::SHIPMENT,
            'locationId' => $locationId
        ]));
        $this->repository->save($this->createMovement([
            'type' => MovementType::SHIPMENT,
            'locationId' => $this->generateUuid()
        ]));

        $found = $this->repository->findByLocationFrom($locationId);

        $this->assertCount(2, $found);
    }

    public function test_find_by_location_to(): void
    {
        $locationId = $this->generateUuid();

        // RECEIPT adds stock to locationId
        $this->repository->save($this->createMovement([
            'type' => MovementType::RECEIPT,
            'locationId' => $locationId
        ]));
        $this->repository->save($this->createMovement([
            'type' => MovementType::RECEIPT,
            'locationId' => $locationId
        ]));
        $this->repository->save($this->createMovement([
            'type' => MovementType::RECEIPT,
            'locationId' => $this->generateUuid()
        ]));

        $found = $this->repository->findByLocationTo($locationId);

        $this->assertCount(2, $found);
    }

    public function test_find_by_type(): void
    {
        $this->repository->save($this->createMovement(['type' => MovementType::RECEIPT]));
        $this->repository->save($this->createMovement(['type' => MovementType::RECEIPT]));
        $this->repository->save($this->createMovement(['type' => MovementType::SHIPMENT]));

        $found = $this->repository->findByType(MovementType::RECEIPT);

        $this->assertCount(2, $found);
    }

    public function test_find_by_status(): void
    {
        $this->repository->save($this->createMovement());
        $this->repository->save($this->createMovement());
        
        $completed = $this->createMovement();
        $completed = $completed->markAsCompleted();
        $this->repository->save($completed);

        $found = $this->repository->findByStatus(MovementStatus::PENDING);

        $this->assertCount(2, $found);
    }

    public function test_find_by_reference(): void
    {
        $reference = 'ORDER-123';

        $this->repository->save($this->createMovement(['referenceId' => $reference]));
        $this->repository->save($this->createMovement(['referenceId' => $reference]));
        $this->repository->save($this->createMovement(['referenceId' => 'OTHER-REF']));

        $found = $this->repository->findByReference($reference);

        $this->assertCount(2, $found);
    }

    public function test_find_by_lot_id(): void
    {
        $lotNumber = 'LOT-123';

        $this->repository->save($this->createMovement(['lotNumber' => $lotNumber]));
        $this->repository->save($this->createMovement(['lotNumber' => $lotNumber]));
        $this->repository->save($this->createMovement(['lotNumber' => 'OTHER-LOT']));

        $found = $this->repository->findByLotId($lotNumber);

        $this->assertCount(2, $found);
    }

    public function test_find_by_date_range(): void
    {
        $now = new DateTimeImmutable();
        $yesterday = $now->modify('-1 day');
        $tomorrow = $now->modify('+1 day');
        $lastWeek = $now->modify('-7 days');

        // Movement created now (default)
        $this->repository->save($this->createMovement());
        
        // Movement created last week
        $this->repository->save($this->createMovementWithDate($lastWeek));

        $found = $this->repository->findByDateRange($yesterday, $tomorrow);

        $this->assertCount(1, $found);
    }

    public function test_delete_existing_movement(): void
    {
        $movement = $this->createMovement();
        $this->repository->save($movement);

        $result = $this->repository->delete($movement->getId());

        $this->assertTrue($result);
        $this->assertNull($this->repository->findById($movement->getId()));
    }

    public function test_delete_nonexistent_returns_false(): void
    {
        $result = $this->repository->delete($this->generateUuid());
        $this->assertFalse($result);
    }

    public function test_all_returns_all_movements(): void
    {
        $this->repository->save($this->createMovement());
        $this->repository->save($this->createMovement());
        $this->repository->save($this->createMovement());

        $all = $this->repository->all();

        $this->assertCount(3, $all);
    }

    public function test_clear_removes_all_movements(): void
    {
        $this->repository->save($this->createMovement());
        $this->repository->save($this->createMovement());

        $this->repository->clear();

        $this->assertEmpty($this->repository->all());
    }

    private function createMovement(array $overrides = []): Movement
    {
        return new Movement(
            id: $overrides['id'] ?? $this->generateUuid(),
            type: $overrides['type'] ?? MovementType::RECEIPT,
            itemId: $overrides['itemId'] ?? $overrides['sku'] ?? 'SKU-' . mt_rand(1000, 9999),
            locationId: $overrides['locationId'] ?? $this->generateUuid(),
            quantity: $overrides['quantity'] ?? 100,
            status: $overrides['status'] ?? MovementStatus::PENDING,
            lotId: $overrides['lotId'] ?? $overrides['lotNumber'] ?? null,
            sourceLocationId: $overrides['sourceLocationId'] ?? null,
            destinationLocationId: $overrides['destinationLocationId'] ?? null,
            referenceType: $overrides['referenceType'] ?? null,
            referenceId: $overrides['referenceId'] ?? null,
            reason: $overrides['reason'] ?? null
        );
    }

    private function createMovementWithDate(DateTimeImmutable $createdAt): Movement
    {
        return new Movement(
            id: $this->generateUuid(),
            type: MovementType::RECEIPT,
            itemId: 'SKU-' . mt_rand(1000, 9999),
            locationId: $this->generateUuid(),
            quantity: 100,
            createdAt: $createdAt
        );
    }
}
