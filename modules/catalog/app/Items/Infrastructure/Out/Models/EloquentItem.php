<?php

namespace App\Items\Infrastructure\Out\Models;

use App\Items\Domain\Entities\Item;
use App\Items\Domain\Interfaces\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentItem extends Model
{
    use SoftDeletes;


}

class EloquentItemRepository implements ItemRepositoryInterface
{
    public function save(Item $item): void
    {
        EloquentItem::updateOrCreate(
            ['id' => $item->getId()],
            [
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'measure_id' => $item->getMeasureId(),
            ]
        );
    }
    public function findById(string $id): Item|null
    {

        $item = EloquentItem::find($id);

        return $item ? new Item(
            id: $item->id,
            name: $item->name,
            measure_id: $item->measure_id,
            description: $item->description
        ) : null;

    }
    public function delete(string $id): void
    {

    }
}