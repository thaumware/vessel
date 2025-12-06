<?php

namespace App\Catalog\Tests\UseCases;

use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Catalog\Domain\UseCases\DeleteItem;
use App\Catalog\Tests\CatalogTestCase;

class DeleteItemTest extends CatalogTestCase
{
    /** @var ItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private DeleteItem $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ItemRepositoryInterface::class);
        $this->useCase = new DeleteItem($this->repository);
    }

    public function test_returns_true_when_item_deleted(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id)
            ->willReturn(true);

        $result = $this->useCase->execute($id);

        $this->assertTrue($result);
    }

    public function test_returns_false_when_item_not_found(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($id)
            ->willReturn(false);

        $result = $this->useCase->execute($id);

        $this->assertFalse($result);
    }

    public function test_calls_repository_with_correct_id(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($id));

        $this->useCase->execute($id);
    }
}
