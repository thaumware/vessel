<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;

class GetItem
{
    public function __construct(
        private ItemRepositoryInterface $repository
    ) {}

    public function execute(string $id): ?Item
    {
        return $this->repository->findById($id);
    }
}
