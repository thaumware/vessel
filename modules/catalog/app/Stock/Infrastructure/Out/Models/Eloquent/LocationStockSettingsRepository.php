<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Stock\Domain\Entities\LocationStockSettings;
use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use DateTimeImmutable;

class LocationStockSettingsRepository implements LocationStockSettingsRepositoryInterface
{
    public function findById(string $id): ?LocationStockSettings
    {
        $model = StockLocationSettingsModel::find($id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByLocationId(string $locationId): ?LocationStockSettings
    {
        $model = StockLocationSettingsModel::where('location_id', $locationId)->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    /**
     * @param string[] $locationIds
     * @return LocationStockSettings[]
     */
    public function findByLocationIds(array $locationIds): array
    {
        $models = StockLocationSettingsModel::whereIn('location_id', $locationIds)->get();

        return $models->map(fn ($model) => $this->toDomain($model))->all();
    }

    public function save(LocationStockSettings $settings): LocationStockSettings
    {
        $data = [
            'id' => $settings->getId(),
            'location_id' => $settings->getLocationId(),
            'max_quantity' => $settings->getMaxQuantity(),
            'max_weight' => $settings->getMaxWeight(),
            'max_volume' => $settings->getMaxVolume(),
            'allowed_item_types' => $settings->getAllowedItemTypes(),
            'allow_mixed_lots' => $settings->allowsMixedLots(),
            'allow_mixed_skus' => $settings->allowsMixedSkus(),
            'fifo_enforced' => $settings->isFifoEnforced(),
            'is_active' => $settings->isActive(),
            'workspace_id' => $settings->getWorkspaceId(),
            'meta' => $settings->getMeta(),
        ];

        StockLocationSettingsModel::updateOrCreate(
            ['id' => $settings->getId()],
            $data
        );

        return $settings;
    }

    public function delete(string $id): bool
    {
        return StockLocationSettingsModel::destroy($id) > 0;
    }

    public function findAllActive(): array
    {
        $models = StockLocationSettingsModel::where('is_active', true)->get();

        return $models->map(fn ($model) => $this->toDomain($model))->all();
    }

    public function existsForLocation(string $locationId): bool
    {
        return StockLocationSettingsModel::where('location_id', $locationId)->exists();
    }

    private function toDomain(StockLocationSettingsModel $model): LocationStockSettings
    {
        return new LocationStockSettings(
            id: $model->id,
            locationId: $model->location_id,
            maxQuantity: $model->max_quantity,
            maxWeight: $model->max_weight,
            maxVolume: $model->max_volume,
            allowedItemTypes: $model->allowed_item_types,
            allowMixedLots: $model->allow_mixed_lots,
            allowMixedSkus: $model->allow_mixed_skus,
            fifoEnforced: $model->fifo_enforced,
            isActive: $model->is_active,
            workspaceId: $model->workspace_id,
            meta: $model->meta,
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
            updatedAt: $model->updated_at
                ? new DateTimeImmutable($model->updated_at->toDateTimeString())
                : null,
        );
    }
}
