<?php

namespace App\Items\Domain\UseCases;

use App\Items\Domain\Entities\Item;
use App\Items\Domain\Interfaces\ItemRepositoryInterface;

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
