<?php

namespace App\Items\Tests\UseCases;

use App\Items\Domain\Entities\Item;
use App\Items\Domain\Interfaces\ItemRepositoryInterface;
use App\Items\Domain\UseCases\ListItems;
use App\Items\Tests\ItemsTestCase;
use App\Shared\Domain\DTOs\PaginatedResult;
use App\Shared\Domain\DTOs\PaginationParams;

class ListItemsTest extends ItemsTestCase
{
    /** @var ItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private ListItems $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ItemRepositoryInterface::class);
        $this->useCase = new ListItems($this->repository);
    }

    public function test_returns_paginated_result(): void
    {
        $params = new PaginationParams(page: 1, perPage: 10);
        $items = [
            new Item(id: $this->generateUuid(), name: 'Item 1'),
            new Item(id: $this->generateUuid(), name: 'Item 2'),
        ];
        $paginatedResult = new PaginatedResult(
            data: $items,
            total: 2,
            page: 1,
            perPage: 10,
            lastPage: 1
        );

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->with($params)
            ->willReturn($paginatedResult);

        $result = $this->useCase->execute($params);

        $this->assertInstanceOf(PaginatedResult::class, $result);
        $this->assertCount(2, $result->data);
        $this->assertEquals(2, $result->total);
    }

    public function test_returns_empty_result_when_no_items(): void
    {
        $params = new PaginationParams(page: 1, perPage: 10);
        $paginatedResult = new PaginatedResult(
            data: [],
            total: 0,
            page: 1,
            perPage: 10,
            lastPage: 1
        );

        $this->repository
            ->method('findAll')
            ->willReturn($paginatedResult);

        $result = $this->useCase->execute($params);

        $this->assertEmpty($result->data);
        $this->assertEquals(0, $result->total);
    }

    public function test_passes_pagination_params_to_repository(): void
    {
        $params = new PaginationParams(page: 2, perPage: 25);

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($params))
            ->willReturn(new PaginatedResult([], 0, 2, 25, 0));

        $this->useCase->execute($params);
    }

    public function test_handles_large_result_set(): void
    {
        $params = new PaginationParams(page: 1, perPage: 100);
        $items = [];
        for ($i = 0; $i < 100; $i++) {
            $items[] = new Item(id: $this->generateUuid(), name: "Item $i");
        }

        $paginatedResult = new PaginatedResult(
            data: $items,
            total: 500,
            page: 1,
            perPage: 100,
            lastPage: 5
        );

        $this->repository
            ->method('findAll')
            ->willReturn($paginatedResult);

        $result = $this->useCase->execute($params);

        $this->assertCount(100, $result->data);
        $this->assertEquals(500, $result->total);
        $this->assertEquals(5, $result->lastPage);
    }

    public function test_default_pagination(): void
    {
        $params = new PaginationParams();

        $this->repository
            ->method('findAll')
            ->willReturn(new PaginatedResult([], 0, 1, 15, 0));

        $result = $this->useCase->execute($params);

        $this->assertEquals(1, $result->page);
        $this->assertEquals(15, $result->perPage);
    }
}
