<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;

class DeleteItem
{
    public function __construct(
        private ItemRepositoryInterface $repository
    ) {}

    public function execute(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
