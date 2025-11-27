<?php

namespace App\Items\Domain\Interfaces;

use App\Items\Domain\Entities\Item;
use App\Shared\Domain\DTOs\PaginatedResult;
use App\Shared\Domain\DTOs\PaginationParams;

interface ItemRepositoryInterface
{
    public function save(Item $item): void;

    public function update(Item $item): void;

    public function findById(string $id): ?Item;

    public function findAll(PaginationParams $params): PaginatedResult;

    public function delete(string $id): bool;
}