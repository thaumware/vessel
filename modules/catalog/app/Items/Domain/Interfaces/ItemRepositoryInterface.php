<?php

namespace App\Items\Domain\Interfaces;

use App\Items\Domain\Entities\Item;

interface ItemRepositoryInterface
{
    public function save(Item $item): void;

    public function findById(string $id): ?Item;

    public function delete(string $id): void;
}