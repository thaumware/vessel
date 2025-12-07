<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Stock\Domain\Entities\Unit;
use App\Stock\Domain\Interfaces\UnitRepositoryInterface;

class UnitRepository implements UnitRepositoryInterface
{
    public function save(Unit $unit): Unit
    {
        $model = UnitModel::updateOrCreate(
            ['id' => $unit->id()],
            ['code' => $unit->code(), 'name' => $unit->name()]
        );

        return new Unit($model->id, $model->code, $model->name);
    }

    public function findById(string $id): ?Unit
    {
        $model = UnitModel::find($id);

        if (!$model) {
            return null;
        }

        return new Unit($model->id, $model->code, $model->name);
    }

    public function findByCode(string $code): ?Unit
    {
        $model = UnitModel::where('code', $code)->first();

        if (!$model) {
            return null;
        }

        return new Unit($model->id, $model->code, $model->name);
    }
}
