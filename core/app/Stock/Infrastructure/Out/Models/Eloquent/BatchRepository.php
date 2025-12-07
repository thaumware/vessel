<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Stock\Domain\Entities\Batch;
use App\Stock\Domain\Interfaces\BatchRepositoryInterface;

class BatchRepository implements BatchRepositoryInterface
{
    public function save(Batch $batch): Batch
    {
        $model = BatchModel::updateOrCreate(
            ['id' => $batch->id()],
            [
                'sku' => $batch->itemId(), // legacy column name, stores itemId
                'location_id' => $batch->locationId(),
                'quantity' => $batch->quantity(),
                'lot_number' => $batch->lotNumber(),
            ]
        );

        return new Batch($model->id, $model->sku, $model->location_id, (int)$model->quantity, $model->lot_number);
    }

    public function findById(string $id): ?Batch
    {
        $model = BatchModel::find($id);

        if (!$model) {
            return null;
        }

        return new Batch($model->id, $model->sku, $model->location_id, (int)$model->quantity, $model->lot_number);
    }

    public function findByItemAndLocation(string $itemId, string $locationId): ?Batch
    {
        $model = BatchModel::where('sku', $itemId)->where('location_id', $locationId)->first();

        if (!$model) {
            return null;
        }

        return new Batch($model->id, $model->sku, $model->location_id, (int)$model->quantity, $model->lot_number);
    }

    /**
     * @deprecated use findByItemAndLocation()
     */
    public function findBySkuAndLocation(string $sku, string $locationId): ?Batch
    {
        return $this->findByItemAndLocation($sku, $locationId);
    }
}
