<?php

namespace App\Items\Domain\UseCases;

use App\Items\Domain\Interfaces\ItemRepositoryInterface;

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
