<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Stock\Domain\Entities\Stock;
use App\Stock\Domain\Interfaces\StockRepositoryInterface;

class StockRepository implements StockRepositoryInterface
{
    public function getByLocation(string $locationId, ?string $locationType = null): array
    {
        $query = StockModel::where('location_id', $locationId);
        if ($locationType !== null) {
            $query->where('location_type', $locationType);
        }

        $models = $query->get();

        $result = [];
        foreach ($models as $m) {
            $result[] = new Stock($m->sku, $m->location_id, $m->location_type ?? null, (int)$m->quantity);
        }

        return $result;
    }

    public function save(Stock $stock): Stock
    {
        $model = StockModel::updateOrCreate(
            ['sku' => $stock->itemId(), 'location_id' => $stock->locationId(), 'location_type' => $stock->locationType()],
            ['quantity' => $stock->quantity()]
        );

        return new Stock($model->sku, $model->location_id, $model->location_type ?? null, (int)$model->quantity);
    }

    public function adjustQuantity(string $itemId, string $locationId, int $delta, ?string $locationType = null): Stock
    {
        // Use DB-level atomic increment/decrement and ensure non-negative quantity
        $attributes = ['sku' => $itemId, 'location_id' => $locationId];
        if ($locationType !== null) {
            $attributes['location_type'] = $locationType;
        }

        $model = StockModel::firstOrNew($attributes);

        $newQuantity = max(0, ($model->quantity ?? 0) + $delta);
        $model->quantity = $newQuantity;
        $model->save();

        return new Stock($model->sku, $model->location_id, $model->location_type ?? null, (int)$model->quantity);
    }
}
