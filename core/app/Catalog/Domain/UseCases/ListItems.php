<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Shared\Domain\DTOs\PaginatedResult;
use App\Shared\Domain\DTOs\PaginationParams;

class ListItems
{
    public function __construct(
        private ItemRepositoryInterface $repository
    ) {}

    public function execute(PaginationParams $params): PaginatedResult
    {
        return $this->repository->findAll($params);
    }
}
