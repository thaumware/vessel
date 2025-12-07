<?php

namespace App\Catalog\Tests\UseCases;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Catalog\Domain\UseCases\GetItem;
use App\Catalog\Tests\CatalogTestCase;

class GetItemTest extends CatalogTestCase
{
    /** @var ItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private GetItem $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ItemRepositoryInterface::class);
        $this->useCase = new GetItem($this->repository);
    }

    public function test_returns_item_when_found(): void
    {
        $data = $this->createItemData();
        $item = new Item(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
        );

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($data['id'])
            ->willReturn($item);

        $result = $this->useCase->execute($data['id']);

        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals($data['id'], $result->getId());
    }

    public function test_returns_null_when_not_found(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn(null);

        $result = $this->useCase->execute($id);

        $this->assertNull($result);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($id));

        $this->useCase->execute($id);
    }
}
