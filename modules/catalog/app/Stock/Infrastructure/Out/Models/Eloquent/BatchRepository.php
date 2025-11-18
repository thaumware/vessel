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
                'sku' => $batch->sku(),
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

    public function findBySkuAndLocation(string $sku, string $locationId): ?Batch
    {
        $model = BatchModel::where('sku', $sku)->where('location_id', $locationId)->first();

        if (!$model) {
            return null;
        }

        return new Batch($model->id, $model->sku, $model->location_id, (int)$model->quantity, $model->lot_number);
    }
}
