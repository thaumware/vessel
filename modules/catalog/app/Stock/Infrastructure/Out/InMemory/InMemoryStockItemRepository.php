<?php

namespace App\Stock\Infrastructure\Out\InMemory;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;

/**
 * InMemory implementation of StockItemRepository
 * 
 * Carga datos desde archivo PHP para testing/desarrollo local
 */
class InMemoryStockItemRepository implements StockItemRepositoryInterface
{
    private array $items = [];

    public function __construct(bool $loadFromFile = true)
    {
        if ($loadFromFile) {
            $this->loadData();
        }
    }

    /**
     * Clear all items (useful for testing)
     */
    public function clear(): void
    {
        $this->items = [];
    }

    private function loadData(): void
    {
        $dataFile = __DIR__ . '/../Data/stock_items.php';
        
        if (file_exists($dataFile)) {
            $data = require $dataFile;
            
            foreach ($data as $row) {
                $this->items[$row['id']] = $this->hydrate($row);
            }
        }
    }

    private function hydrate(array $data): StockItem
    {
        return new StockItem(
            id: $data['id'],
            sku: $data['sku'],
            catalogItemId: $data['catalog_item_id'],
            catalogOrigin: $data['catalog_origin'],
            locationId: $data['location_id'],
            locationType: $data['location_type'] ?? null,
            quantity: $data['quantity'] ?? 0,
            reservedQuantity: $data['reserved_quantity'] ?? 0,
            lotNumber: $data['lot_number'] ?? null,
            expirationDate: isset($data['expiration_date']) 
                ? new \DateTimeImmutable($data['expiration_date']) 
                : null,
            serialNumber: $data['serial_number'] ?? null,
            workspaceId: $data['workspace_id'] ?? null,
            meta: $data['meta'] ?? null,
            createdAt: isset($data['created_at']) 
                ? new \DateTimeImmutable($data['created_at']) 
                : null,
            updatedAt: isset($data['updated_at']) 
                ? new \DateTimeImmutable($data['updated_at']) 
                : null,
        );
    }

    public function findById(string $id): ?StockItem
    {
        return $this->items[$id] ?? null;
    }

    public function findBySku(string $sku): array
    {
        return array_values(array_filter(
            $this->items,
            fn(StockItem $item) => $item->getSku() === $sku
        ));
    }

    public function findBySkuAndLocation(string $sku, string $locationId): ?StockItem
    {
        foreach ($this->items as $item) {
            if ($item->getSku() === $sku && $item->getLocationId() === $locationId) {
                return $item;
            }
        }
        return null;
    }

    public function findByLocation(string $locationId): array
    {
        return array_values(array_filter(
            $this->items,
            fn(StockItem $item) => $item->getLocationId() === $locationId
        ));
    }

    public function findByCatalogItemId(string $catalogItemId, string $catalogOrigin): array
    {
        return array_values(array_filter(
            $this->items,
            fn(StockItem $item) => 
                $item->getCatalogItemId() === $catalogItemId && 
                $item->getCatalogOrigin() === $catalogOrigin
        ));
    }

    public function search(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $result = $this->items;

        if (isset($filters['location_id'])) {
            $result = array_filter($result, fn($item) => $item->getLocationId() === $filters['location_id']);
        }

        if (isset($filters['sku'])) {
            $result = array_filter($result, fn($item) => $item->getSku() === $filters['sku']);
        }

        if (isset($filters['catalog_item_id'])) {
            $result = array_filter($result, fn($item) => $item->getCatalogItemId() === $filters['catalog_item_id']);
        }

        if (isset($filters['catalog_origin'])) {
            $result = array_filter($result, fn($item) => $item->getCatalogOrigin() === $filters['catalog_origin']);
        }

        return array_slice(array_values($result), $offset, $limit);
    }

    public function save(StockItem $stockItem): StockItem
    {
        $this->items[$stockItem->getId()] = $stockItem;
        return $stockItem;
    }

    public function update(StockItem $stockItem): StockItem
    {
        $this->items[$stockItem->getId()] = $stockItem;
        return $stockItem;
    }

    public function delete(string $id): void
    {
        unset($this->items[$id]);
    }

    public function adjustQuantity(string $sku, string $locationId, int $delta): StockItem
    {
        $item = $this->findBySkuAndLocation($sku, $locationId);

        if (!$item) {
            throw new \RuntimeException("StockItem not found for SKU: {$sku} at location: {$locationId}");
        }

        $updated = $item->adjustQuantity($delta);
        $this->items[$updated->getId()] = $updated;

        return $updated;
    }

    public function reserve(string $id, int $quantity): StockItem
    {
        $item = $this->findById($id);

        if (!$item) {
            throw new \RuntimeException("StockItem not found: {$id}");
        }

        $updated = $item->reserve($quantity);
        $this->items[$id] = $updated;

        return $updated;
    }

    public function release(string $id, int $quantity): StockItem
    {
        $item = $this->findById($id);

        if (!$item) {
            throw new \RuntimeException("StockItem not found: {$id}");
        }

        $updated = $item->release($quantity);
        $this->items[$id] = $updated;

        return $updated;
    }

    public function findWithCatalogItems(array $ids): array
    {
        return array_values(array_filter(
            $this->items,
            fn(StockItem $item) => in_array($item->getId(), $ids)
        ));
    }
}
