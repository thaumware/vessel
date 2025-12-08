<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use Illuminate\Support\Str;

class StockItemRepository implements StockItemRepositoryInterface
{
    public function findById(string $id): ?StockItem
    {
        $model = StockItemModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByItemId(string $itemId): array
    {
        return StockItemModel::where('sku', $itemId)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findByItemAndLocation(string $itemId, string $locationId): ?StockItem
    {
        $model = StockItemModel::where('sku', $itemId)
            ->where('location_id', $locationId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByLocation(string $locationId): array
    {
        return StockItemModel::where('location_id', $locationId)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findByCatalogItemId(string $catalogItemId, string $catalogOrigin): array
    {
        return StockItemModel::where('catalog_item_id', $catalogItemId)
            ->where('catalog_origin', $catalogOrigin)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->toArray();
    }

    public function search(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $query = StockItemModel::query();

        if (isset($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (isset($filters['item_id'])) {
            $query->where('sku', $filters['item_id']);
        }

        if (isset($filters['catalog_item_id'])) {
            $query->where('catalog_item_id', $filters['catalog_item_id']);
        }

        if (isset($filters['catalog_origin'])) {
            $query->where('catalog_origin', $filters['catalog_origin']);
        }

        return $query
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->toArray();
    }

    public function save(StockItem $stockItem): StockItem
    {
        StockItemModel::updateOrCreate(
            ['id' => $stockItem->getId()],
            [
                'sku' => $stockItem->getItemId(),
                'catalog_item_id' => $stockItem->getCatalogItemId(),
                'catalog_origin' => $stockItem->getCatalogOrigin(),
                'location_id' => $stockItem->getLocationId(),
                'location_type' => $stockItem->getLocationType(),
                'status_id' => null,
                'item_type' => 'unit',
                'item_id' => $stockItem->getId(),
                'quantity' => $stockItem->getQuantity(),
                'reserved_quantity' => $stockItem->getReservedQuantity(),
                'lot_number' => $stockItem->getLotNumber(),
                'expiration_date' => $stockItem->getExpirationDate()?->format('Y-m-d'),
                'serial_number' => $stockItem->getSerialNumber(),
                'workspace_id' => $stockItem->getWorkspaceId(),
                'meta' => $stockItem->getMeta(),
            ]
        );

        return $stockItem;
    }

    public function update(StockItem $stockItem): StockItem
    {
        StockItemModel::where('id', $stockItem->getId())->update([
            'sku' => $stockItem->getItemId(),
            'catalog_item_id' => $stockItem->getCatalogItemId(),
            'catalog_origin' => $stockItem->getCatalogOrigin(),
            'location_id' => $stockItem->getLocationId(),
            'location_type' => $stockItem->getLocationType(),
            'item_type' => 'unit',
            'item_id' => $stockItem->getId(),
            'quantity' => $stockItem->getQuantity(),
            'reserved_quantity' => $stockItem->getReservedQuantity(),
            'lot_number' => $stockItem->getLotNumber(),
            'expiration_date' => $stockItem->getExpirationDate()?->format('Y-m-d'),
            'serial_number' => $stockItem->getSerialNumber(),
            'workspace_id' => $stockItem->getWorkspaceId(),
            'meta' => $stockItem->getMeta(),
        ]);

        return $stockItem;
    }

    public function delete(string $id): void
    {
        StockItemModel::destroy($id);
    }

    public function adjustQuantity(string $itemId, string $locationId, int $delta): StockItem
    {
        $model = StockItemModel::where('sku', $itemId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if (!$model) {
            throw new \RuntimeException("StockItem not found for item: {$itemId} at location: {$locationId}");
        }

        $model->quantity = $model->quantity + $delta;
        $model->save();

        return $this->toDomain($model);
    }

    public function reserve(string $id, int $quantity): StockItem
    {
        $model = StockItemModel::lockForUpdate()->findOrFail($id);
        
        $available = $model->quantity - $model->reserved_quantity;
        if ($quantity > $available) {
            throw new \DomainException("Cannot reserve {$quantity} units. Only {$available} available.");
        }

        $model->reserved_quantity = $model->reserved_quantity + $quantity;
        $model->save();

        return $this->toDomain($model);
    }

    public function release(string $id, int $quantity): StockItem
    {
        $model = StockItemModel::lockForUpdate()->findOrFail($id);
        
        if ($quantity > $model->reserved_quantity) {
            throw new \DomainException("Cannot release {$quantity} units. Only {$model->reserved_quantity} reserved.");
        }

        $model->reserved_quantity = $model->reserved_quantity - $quantity;
        $model->save();

        return $this->toDomain($model);
    }

    public function findWithCatalogItems(array $ids): array
    {
        return StockItemModel::whereIn('id', $ids)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->toArray();
    }

    private function toDomain(StockItemModel $model): StockItem
    {
        return new StockItem(
            id: $model->id,
            itemId: $model->sku,
            catalogItemId: $model->catalog_item_id,
            catalogOrigin: $model->catalog_origin,
            locationId: $model->location_id,
            locationType: $model->location_type,
            quantity: $model->quantity,
            reservedQuantity: $model->reserved_quantity ?? 0,
            lotNumber: $model->lot_number,
            expirationDate: $model->expiration_date 
                ? new \DateTimeImmutable($model->expiration_date->format('Y-m-d')) 
                : null,
            serialNumber: $model->serial_number,
            workspaceId: $model->workspace_id,
            meta: $model->meta,
            createdAt: new \DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new \DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
