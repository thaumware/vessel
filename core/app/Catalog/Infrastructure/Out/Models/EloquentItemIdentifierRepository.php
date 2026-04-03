<?php

namespace App\Catalog\Infrastructure\Out\Models;

use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Domain\Interfaces\ItemIdentifierRepositoryInterface;

class EloquentItemIdentifierRepository implements ItemIdentifierRepositoryInterface
{
    public function save(ItemIdentifier $identifier): void
    {
        EloquentItemIdentifier::query()->updateOrCreate(
            ['id' => $identifier->getId()],
            [
                'item_id' => $identifier->itemId(),
                'variant_id' => $identifier->variantId(),
                'type' => $identifier->type()->value,
                'value' => $identifier->value(),
                'is_primary' => $identifier->isPrimary(),
            ]
        );
    }

    public function findById(string $id): ?ItemIdentifier
    {
        $model = EloquentItemIdentifier::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByTypeAndValue(string $type, string $value): ?ItemIdentifier
    {
        $model = EloquentItemIdentifier::query()
            ->where('type', $type)
            ->where('value', $value)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByItemAndType(string $itemId, string $type, ?string $variantId = null): ?ItemIdentifier
    {
        $query = EloquentItemIdentifier::query()
            ->where('item_id', $itemId)
            ->where('type', $type);

        if ($variantId === null) {
            $query->whereNull('variant_id');
        } else {
            $query->where('variant_id', $variantId);
        }

        $model = $query->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByValueForTypes(string $value, array $types): array
    {
        return EloquentItemIdentifier::query()
            ->where('value', $value)
            ->whereIn('type', $types)
            ->get()
            ->map(fn (EloquentItemIdentifier $model) => $this->toDomain($model))
            ->all();
    }

    private function toDomain(EloquentItemIdentifier $model): ItemIdentifier
    {
        return new ItemIdentifier(
            id: $model->id,
            item_id: $model->item_id,
            type: $model->type,
            value: $model->value,
            is_primary: (bool) $model->is_primary,
            variant_id: $model->variant_id,
            label: null
        );
    }
}
