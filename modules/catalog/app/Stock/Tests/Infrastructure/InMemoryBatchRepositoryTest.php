<?php

namespace App\Stock\Tests\Infrastructure;

use App\Stock\Domain\Entities\Batch;
use App\Stock\Infrastructure\Out\InMemory\InMemoryBatchRepository;
use App\Stock\Tests\StockTestCase;

class InMemoryBatchRepositoryTest extends StockTestCase
{
    private InMemoryBatchRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryBatchRepository();
    }

    public function test_save_and_find_by_id(): void
    {
        $data = $this->createBatchData();
        $batch = new Batch(
            id: $data['id'],
            itemId: $data['sku'],
            locationId: $data['locationId'],
            quantity: $data['quantity'],
            lotNumber: $data['lotNumber']
        );

        $this->repository->save($batch);
        $found = $this->repository->findById($data['id']);

        $this->assertNotNull($found);
        $this->assertEquals($data['id'], $found->id());
        $this->assertEquals($data['sku'], $found->itemId());
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->repository->findById($this->generateUuid());
        $this->assertNull($found);
    }

    public function test_find_by_sku_and_location(): void
    {
        $itemId = 'TEST-SKU';
        $locationId = $this->generateUuid();

        $batch = new Batch(
            id: $this->generateUuid(),
            itemId: $itemId,
            locationId: $locationId,
            quantity: 100
        );

        $this->repository->save($batch);
        $found = $this->repository->findByItemAndLocation($itemId, $locationId);

        $this->assertNotNull($found);
        $this->assertEquals($itemId, $found->itemId());
        $this->assertEquals($locationId, $found->locationId());
    }

    public function test_find_by_sku_and_location_returns_null_when_not_found(): void
    {
        $found = $this->repository->findByItemAndLocation('NONEXISTENT', $this->generateUuid());
        $this->assertNull($found);
    }

    public function test_find_by_sku(): void
    {
        $itemId = 'COMMON-SKU';

        $this->repository->save(new Batch($this->generateUuid(), $itemId, $this->generateUuid(), 50));
        $this->repository->save(new Batch($this->generateUuid(), $itemId, $this->generateUuid(), 30));
        $this->repository->save(new Batch($this->generateUuid(), 'OTHER-SKU', $this->generateUuid(), 20));

        $found = $this->repository->findBySku($itemId);

        $this->assertCount(2, $found);
        foreach ($found as $batch) {
            $this->assertEquals($itemId, $batch->itemId());
        }
    }

    public function test_find_by_location(): void
    {
        $locationId = $this->generateUuid();

        $this->repository->save(new Batch($this->generateUuid(), 'SKU-1', $locationId, 50));
        $this->repository->save(new Batch($this->generateUuid(), 'SKU-2', $locationId, 30));
        $this->repository->save(new Batch($this->generateUuid(), 'SKU-3', $this->generateUuid(), 20));

        $found = $this->repository->findByLocation($locationId);

        $this->assertCount(2, $found);
        foreach ($found as $batch) {
            $this->assertEquals($locationId, $batch->locationId());
        }
    }

    public function test_find_by_lot_number(): void
    {
        $lotNumber = 'LOT-2024-001';

        $this->repository->save(new Batch($this->generateUuid(), 'SKU-1', $this->generateUuid(), 50, $lotNumber));
        $this->repository->save(new Batch($this->generateUuid(), 'SKU-2', $this->generateUuid(), 30, $lotNumber));
        $this->repository->save(new Batch($this->generateUuid(), 'SKU-3', $this->generateUuid(), 20, 'OTHER-LOT'));

        $found = $this->repository->findByLotNumber($lotNumber);

        $this->assertCount(2, $found);
        foreach ($found as $batch) {
            $this->assertEquals($lotNumber, $batch->lotNumber());
        }
    }

    public function test_delete_existing_batch(): void
    {
        $batch = new Batch($this->generateUuid(), 'SKU', $this->generateUuid(), 100);
        $this->repository->save($batch);

        $result = $this->repository->delete($batch->id());

        $this->assertTrue($result);
        $this->assertNull($this->repository->findById($batch->id()));
    }

    public function test_delete_nonexistent_returns_false(): void
    {
        $result = $this->repository->delete($this->generateUuid());
        $this->assertFalse($result);
    }

    public function test_clear_removes_all_batches(): void
    {
        $this->repository->save(new Batch($this->generateUuid(), 'SKU-1', $this->generateUuid(), 50));
        $this->repository->save(new Batch($this->generateUuid(), 'SKU-1', $this->generateUuid(), 50));
        $this->repository->save(new Batch($this->generateUuid(), 'SKU-2', $this->generateUuid(), 30));
        $this->repository->clear();
        $this->assertEmpty($this->repository->findBySku('SKU-1'));
        $this->assertEmpty($this->repository->findBySku('SKU-2'));
        $this->assertEmpty($this->repository->findBySku('SKU-2'));
    }
}
