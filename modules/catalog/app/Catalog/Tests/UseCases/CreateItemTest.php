<?php

namespace App\Catalog\Tests\UseCases;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Catalog\Domain\UseCases\CreateItem;
use App\Catalog\Tests\CatalogTestCase;

class CreateItemTest extends CatalogTestCase
{
    /** @var ItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private CreateItem $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ItemRepositoryInterface::class);
        $this->useCase = new CreateItem($this->repository);
    }

    public function test_can_create_item_with_all_fields(): void
    {
        $data = $this->createItemData();

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Item $item) use ($data) {
                return $item->getId() === $data['id']
                    && $item->getName() === $data['name']
                    && $item->getDescription() === $data['description']
                    && $item->getUomId() === $data['uomId']
                        && $item->getNotes() === $data['notes']
                        && $item->getStatusValue() === $data['status']
                        && $item->getWorkspaceId() === $data['workspaceId'];
            }));

        $result = $this->useCase->execute(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
            uomId: $data['uomId'],
            notes: $data['notes'],
            status: $data['status'],
            workspaceId: $data['workspaceId'],
        );

        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals($data['id'], $result->getId());
        $this->assertEquals($data['name'], $result->getName());
    }

    public function test_can_create_item_with_minimal_fields(): void
    {
        $id = $this->generateUuid();
        $name = 'Minimal Item';

        $this->repository
            ->expects($this->once())
            ->method('save');

        $result = $this->useCase->execute(
            id: $id,
            name: $name
        );

        $this->assertEquals($id, $result->getId());
        $this->assertEquals($name, $result->getName());
        $this->assertNull($result->getDescription());
        $this->assertEquals('active', $result->getStatusValue());
    }

    public function test_defaults_to_active_status(): void
    {
        $this->repository->method('save');

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Test Item'
        );

        $this->assertEquals('active', $result->getStatusValue());
    }

    public function test_can_create_draft_item(): void
    {
        $this->repository->method('save');

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            name: 'Draft Item',
            status: 'draft'
        );

        $this->assertEquals('draft', $result->getStatusValue());
    }

    public function test_can_create_item_with_terms(): void
    {
        $this->markTestSkipped('Taxonomy terms now handled by ItemClassification service, not Item entity');
    }

    public function test_returns_created_item(): void
    {
        $data = $this->createItemData();

        $this->repository->method('save');

        $result = $this->useCase->execute(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
        );

        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals($data['id'], $result->getId());
    }
}
