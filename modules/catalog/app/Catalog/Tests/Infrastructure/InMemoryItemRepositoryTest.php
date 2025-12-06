<?php

namespace App\Catalog\Tests\Infrastructure;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Infrastructure\Out\InMemory\InMemoryItemRepository;
use App\Catalog\Tests\CatalogTestCase;
use App\Shared\Domain\DTOs\PaginationParams;

class InMemoryItemRepositoryTest extends CatalogTestCase
{
    private InMemoryItemRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        // Create repository without loading data file
        $this->repository = new class extends InMemoryItemRepository {
            public function __construct()
            {
                // Skip parent constructor to avoid loading data file
            }
        };
    }

    public function test_save_and_find_by_id(): void
    {
        $item = new Item(
            id: $this->generateUuid(),
            name: 'Test Item'
        );

        $this->repository->save($item);
        $found = $this->repository->findById($item->getId());

        $this->assertNotNull($found);
        $this->assertEquals($item->getId(), $found->getId());
        $this->assertEquals($item->getName(), $found->getName());
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->repository->findById($this->generateUuid());
        $this->assertNull($found);
    }

    public function test_update_existing_item(): void
    {
        $id = $this->generateUuid();
        $item = new Item(id: $id, name: 'Original');
        $this->repository->save($item);

        $updated = new Item(id: $id, name: 'Updated');
        $this->repository->update($updated);

        $found = $this->repository->findById($id);
        $this->assertEquals('Updated', $found->getName());
    }

    public function test_delete_existing_item(): void
    {
        $item = new Item(id: $this->generateUuid(), name: 'To Delete');
        $this->repository->save($item);

        $result = $this->repository->delete($item->getId());

        $this->assertTrue($result);
        $this->assertNull($this->repository->findById($item->getId()));
    }

    public function test_delete_nonexistent_returns_false(): void
    {
        $result = $this->repository->delete($this->generateUuid());
        $this->assertFalse($result);
    }

    public function test_find_all_with_pagination(): void
    {
        // Add multiple items
        for ($i = 1; $i <= 25; $i++) {
            $this->repository->save(new Item(
                id: $this->generateUuid(),
                name: "Item $i"
            ));
        }

        $params = new PaginationParams(page: 1, perPage: 10);
        $result = $this->repository->findAll($params);

        $this->assertCount(10, $result->data);
        $this->assertEquals(25, $result->total);
        $this->assertEquals(1, $result->page);
        $this->assertEquals(10, $result->perPage);
        $this->assertEquals(3, $result->lastPage);
    }

    public function test_find_all_second_page(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->repository->save(new Item(
                id: $this->generateUuid(),
                name: "Item $i"
            ));
        }

        $params = new PaginationParams(page: 2, perPage: 10);
        $result = $this->repository->findAll($params);

        $this->assertCount(10, $result->data);
        $this->assertEquals(2, $result->page);
    }

    public function test_find_all_last_page_partial(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->repository->save(new Item(
                id: $this->generateUuid(),
                name: "Item $i"
            ));
        }

        $params = new PaginationParams(page: 3, perPage: 10);
        $result = $this->repository->findAll($params);

        $this->assertCount(5, $result->data); // Only 5 items left
        $this->assertEquals(3, $result->page);
    }

    public function test_find_all_empty(): void
    {
        $params = new PaginationParams(page: 1, perPage: 10);
        $result = $this->repository->findAll($params);

        $this->assertEmpty($result->data);
        $this->assertEquals(0, $result->total);
    }

    public function test_save_preserves_all_fields(): void
    {
        $data = $this->createItemData();
        $item = new Item(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
            uomId: $data['uomId'],
            notes: $data['notes'],
            status: $data['status'],
            workspaceId: $data['workspaceId'],
        );

        $this->repository->save($item);
        $found = $this->repository->findById($data['id']);

        $this->assertEquals($data['name'], $found->getName());
        $this->assertEquals($data['description'], $found->getDescription());
        $this->assertEquals($data['uomId'], $found->getUomId());
        $this->assertEquals($data['notes'], $found->getNotes());
        $this->assertEquals($data['status'], $found->getStatusValue());
        $this->assertEquals($data['workspaceId'], $found->getWorkspaceId());
    }

    public function test_save_replaces_existing_item_with_same_id(): void
    {
        $id = $this->generateUuid();

        $item1 = new Item(id: $id, name: 'First');
        $this->repository->save($item1);

        $item2 = new Item(id: $id, name: 'Second');
        $this->repository->save($item2);

        $found = $this->repository->findById($id);
        $this->assertEquals('Second', $found->getName());

        // Should only have one item
        $all = $this->repository->findAll(new PaginationParams());
        $this->assertEquals(1, $all->total);
    }
}
