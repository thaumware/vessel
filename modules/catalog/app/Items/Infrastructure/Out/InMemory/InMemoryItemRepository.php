<?php

namespace App\Items\Infrastructure\Out\InMemory;

use App\Items\Domain\Entities\Item;
use App\Items\Domain\Interfaces\ItemRepositoryInterface;
use App\Shared\Domain\DTOs\PaginatedResult;
use App\Shared\Domain\DTOs\PaginationParams;

class InMemoryItemRepository implements ItemRepositoryInterface
{
    private array $items = [];

    public function __construct()
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $dataFile = __DIR__ . '/../Data/items.php';

        if (file_exists($dataFile)) {
            $data = require $dataFile;

            foreach ($data as $itemData) {
                $item = new Item(
                    id: $itemData['id'],
                    name: $itemData['name'],
                    description: $itemData['description'] ?? null,
                    uomId: $itemData['uom_id'] ?? null,
                    notes: $itemData['notes'] ?? null,
                    status: $itemData['status'] ?? 'active',
                    workspaceId: $itemData['workspace_id'] ?? null,
                    termIds: $itemData['term_ids'] ?? [],
                );
                $this->items[$item->getId()] = $item;
            }
        }
    }

    public function save(Item $item): void
    {
        $this->items[$item->getId()] = $item;
    }

    public function update(Item $item): void
    {
        $this->items[$item->getId()] = $item;
    }

    public function findById(string $id): ?Item
    {
        return $this->items[$id] ?? null;
    }

    public function findAll(PaginationParams $params): PaginatedResult
    {
        $allItems = array_values($this->items);
        $total = count($allItems);

        $offset = ($params->page - 1) * $params->perPage;
        $paginatedItems = array_slice($allItems, $offset, $params->perPage);
        $lastPage = (int) ceil($total / $params->perPage);

        return new PaginatedResult(
            data: $paginatedItems,
            total: $total,
            page: $params->page,
            perPage: $params->perPage,
            lastPage: $lastPage
        );
    }

    public function delete(string $id): bool
    {
        if (!isset($this->items[$id])) {
            return false;
        }

        unset($this->items[$id]);
        return true;
    }
}
