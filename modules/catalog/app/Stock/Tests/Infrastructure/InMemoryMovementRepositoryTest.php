<?php

namespace App\Stock\Tests\Infrastructure;

use App\Stock\Domain\Entities\Movement;
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
        $data = $this->createMovementData();
        $movement = new Movement(
            id: $data['id'],
            movementId: $data['movementId'],
            sku: $data['sku'],
            locationFromId: $data['locationFromId'],
            locationFromType: $data['locationFromType'],
            locationToId: $data['locationToId'],
            locationToType: $data['locationToType'],
            quantity: $data['quantity']
        );

        $this->repository->save($movement);
        $found = $this->repository->findById($data['id']);

        $this->assertNotNull($found);
        $this->assertEquals($data['id'], $found->getId());
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->repository->findById($this->generateUuid());
        $this->assertNull($found);
    }

    public function test_find_by_movement_id(): void
    {
        $movementId = 'MOV-UNIQUE-123';
        $movement = new Movement(
            id: $this->generateUuid(),
            movementId: $movementId,
            sku: 'SKU-001',
            locationFromId: null,
            locationFromType: null,
            locationToId: $this->generateUuid(),
            locationToType: 'warehouse',
            quantity: 100
        );

        $this->repository->save($movement);
        $found = $this->repository->findByMovementId($movementId);

        $this->assertNotNull($found);
        $this->assertEquals($movementId, $found->getMovementId());
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

        $this->repository->save($this->createMovement(['locationFromId' => $locationId]));
        $this->repository->save($this->createMovement(['locationFromId' => $locationId]));
        $this->repository->save($this->createMovement(['locationFromId' => $this->generateUuid()]));

        $found = $this->repository->findByLocationFrom($locationId);

        $this->assertCount(2, $found);
    }

    public function test_find_by_location_to(): void
    {
        $locationId = $this->generateUuid();

        $this->repository->save($this->createMovement(['locationToId' => $locationId]));
        $this->repository->save($this->createMovement(['locationToId' => $locationId]));
        $this->repository->save($this->createMovement(['locationToId' => $this->generateUuid()]));

        $found = $this->repository->findByLocationTo($locationId);

        $this->assertCount(2, $found);
    }

    public function test_find_by_type(): void
    {
        $this->repository->save($this->createMovement(['movementType' => 'incoming']));
        $this->repository->save($this->createMovement(['movementType' => 'incoming']));
        $this->repository->save($this->createMovement(['movementType' => 'outgoing']));

        $found = $this->repository->findByType('incoming');

        $this->assertCount(2, $found);
    }

    public function test_find_by_reference(): void
    {
        $reference = 'ORDER-123';

        $this->repository->save($this->createMovement(['reference' => $reference]));
        $this->repository->save($this->createMovement(['reference' => $reference]));
        $this->repository->save($this->createMovement(['reference' => 'OTHER-REF']));

        $found = $this->repository->findByReference($reference);

        $this->assertCount(2, $found);
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
        $data = $this->createMovementData($overrides);

        return new Movement(
            id: $data['id'],
            movementId: $data['movementId'],
            sku: $data['sku'],
            locationFromId: $data['locationFromId'],
            locationFromType: $data['locationFromType'],
            locationToId: $data['locationToId'],
            locationToType: $data['locationToType'],
            quantity: $data['quantity'],
            balanceAfter: $data['balanceAfter'] ?? null,
            movementType: $data['movementType'] ?? null,
            reference: $data['reference'] ?? null
        );
    }
}
