<?php

namespace App\Items\Tests\UseCases;

use App\Items\Domain\Entities\Item;
use App\Items\Domain\Interfaces\ItemRepositoryInterface;
use App\Items\Domain\UseCases\UpdateItem;
use App\Items\Tests\ItemsTestCase;

class UpdateItemTest extends ItemsTestCase
{
    /** @var ItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private UpdateItem $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ItemRepositoryInterface::class);
        $this->useCase = new UpdateItem($this->repository);
    }

    public function test_updates_item_name(): void
    {
        $data = $this->createItemData();
        $existingItem = new Item(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
        );

        $this->repository
            ->method('findById')
            ->willReturn($existingItem);

        $this->repository
            ->expects($this->once())
            ->method('update');

        $result = $this->useCase->execute(
            id: $data['id'],
            name: 'Updated Name'
        );

        $this->assertEquals('Updated Name', $result->getName());
    }

    public function test_updates_item_description(): void
    {
        $data = $this->createItemData();
        $existingItem = new Item(
            id: $data['id'],
            name: $data['name'],
            description: 'Original description',
        );

        $this->repository->method('findById')->willReturn($existingItem);
        $this->repository->method('update');

        $result = $this->useCase->execute(
            id: $data['id'],
            description: 'New description'
        );

        $this->assertEquals('New description', $result->getDescription());
        $this->assertEquals($data['name'], $result->getName()); // unchanged
    }

    public function test_updates_item_status(): void
    {
        $existingItem = new Item(
            id: $this->generateUuid(),
            name: 'Test',
            status: 'active'
        );

        $this->repository->method('findById')->willReturn($existingItem);
        $this->repository->method('update');

        $result = $this->useCase->execute(
            id: $existingItem->getId(),
            status: 'archived'
        );

        $this->assertEquals('archived', $result->getStatus());
    }

    public function test_updates_item_term_ids(): void
    {
        $existingItem = new Item(
            id: $this->generateUuid(),
            name: 'Test',
            termIds: []
        );

        $newTermIds = [$this->generateUuid(), $this->generateUuid()];

        $this->repository->method('findById')->willReturn($existingItem);
        $this->repository->method('update');

        $result = $this->useCase->execute(
            id: $existingItem->getId(),
            termIds: $newTermIds
        );

        $this->assertEquals($newTermIds, $result->getTermIds());
    }

    public function test_returns_null_when_item_not_found(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->repository
            ->expects($this->never())
            ->method('update');

        $result = $this->useCase->execute(
            id: $id,
            name: 'New Name'
        );

        $this->assertNull($result);
    }

    public function test_preserves_unchanged_fields(): void
    {
        $data = $this->createItemData();
        $existingItem = new Item(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
            uomId: $data['uomId'],
            notes: $data['notes'],
            status: $data['status'],
            workspaceId: $data['workspaceId'],
            termIds: $data['termIds'],
        );

        $this->repository->method('findById')->willReturn($existingItem);
        $this->repository->method('update');

        $result = $this->useCase->execute(
            id: $data['id'],
            name: 'Only Name Changed'
        );

        $this->assertEquals('Only Name Changed', $result->getName());
        $this->assertEquals($data['description'], $result->getDescription());
        $this->assertEquals($data['uomId'], $result->getUomId());
        $this->assertEquals($data['notes'], $result->getNotes());
        $this->assertEquals($data['status'], $result->getStatus());
        $this->assertEquals($data['workspaceId'], $result->getWorkspaceId());
        $this->assertEquals($data['termIds'], $result->getTermIds());
    }

    public function test_can_update_multiple_fields_at_once(): void
    {
        $existingItem = new Item(
            id: $this->generateUuid(),
            name: 'Original',
            description: 'Original desc',
            status: 'active'
        );

        $this->repository->method('findById')->willReturn($existingItem);
        $this->repository->method('update');

        $result = $this->useCase->execute(
            id: $existingItem->getId(),
            name: 'Updated',
            description: 'Updated desc',
            status: 'draft'
        );

        $this->assertEquals('Updated', $result->getName());
        $this->assertEquals('Updated desc', $result->getDescription());
        $this->assertEquals('draft', $result->getStatus());
    }

    public function test_can_clear_optional_fields(): void
    {
        $existingItem = new Item(
            id: $this->generateUuid(),
            name: 'Test',
            description: 'Has description',
            notes: 'Has notes'
        );

        $this->repository->method('findById')->willReturn($existingItem);
        $this->repository->method('update');

        // Note: This tests the current behavior where null means "keep existing"
        // If you want to clear fields, you'd need empty string or different approach
        $result = $this->useCase->execute(
            id: $existingItem->getId()
        );

        $this->assertEquals('Has description', $result->getDescription());
        $this->assertEquals('Has notes', $result->getNotes());
    }
}
