<?php

namespace App\Catalog\Infrastructure\Out\Models;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Shared\Domain\DTOs\PaginatedResult;
use App\Shared\Domain\DTOs\PaginationParams;
use Illuminate\Support\Facades\DB;

class EloquentItemRepository implements ItemRepositoryInterface
{
    public function save(Item $item): void
    {
        $model = EloquentItem::create([
            'id' => $item->getId(),
            'name' => $item->getName(),
            'description' => $item->getDescription(),
            'uom_id' => $item->getUomId(),
            'notes' => $item->getNotes(),
            'status' => $item->getStatus(),
            'workspace_id' => $item->getWorkspaceId(),
        ]);

    }

    public function update(Item $item): void
    {
        $model = EloquentItem::find($item->getId());
        
        if ($model) {
            $model->update([
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'uom_id' => $item->getUomId(),
                'notes' => $item->getNotes(),
                'status' => $item->getStatus(),
            ]);

        }
    }

    public function findById(string $id): ?Item
    {
        $model = EloquentItem::find($id);

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findAll(PaginationParams $params): PaginatedResult
    {
        $query = EloquentItem::query();
        
        $total = $query->count();
        $lastPage = (int) ceil($total / $params->perPage);
        
        $models = $query
            ->skip(($params->page - 1) * $params->perPage)
            ->take($params->perPage)
            ->get();

        $items = $models->map(fn($model) => $this->toDomain($model));

        return new PaginatedResult(
            data: $items->toArray(),
            total: $total,
            page: $params->page,
            perPage: $params->perPage,
            lastPage: $lastPage
        );
    }

    public function delete(string $id): bool
    {
        $model = EloquentItem::find($id);
        
        if (!$model) {
            return false;
        }

        $model->delete();
        return true;
    }

    private function toDomain(EloquentItem $model): Item
    {
        return new Item(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            uomId: $model->uom_id,
            notes: $model->notes,
            status: $model->status ?? 'active',
            workspaceId: $model->workspace_id,
        );
    }

    /**
     * Convierte Item a array enriquecido con term_ids.
     */
    private function toArrayWithTerms(Item $item): array
    {
        $array = $item->toArray();
        
        // Cargar term_ids desde la tabla pivot
        $termIds = DB::table('catalog_item_terms')
            ->where('item_id', $item->getId())
            ->whereNull('deleted_at')
            ->pluck('term_id')
            ->toArray();
        
        $array['term_ids'] = $termIds;
        
        return $array;
    }
}