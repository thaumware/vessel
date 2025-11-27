<?php

namespace App\Items\Infrastructure\Out\Models;

use App\Items\Domain\Entities\Item;
use App\Items\Domain\Interfaces\ItemRepositoryInterface;
use App\Shared\Domain\DTOs\PaginatedResult;
use App\Shared\Domain\DTOs\PaginationParams;

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

        // Sync M:M relationship with terms
        if (!empty($item->getTermIds())) {
            $model->terms()->sync($item->getTermIds());
        }
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

            // Sync M:M relationship with terms
            $model->terms()->sync($item->getTermIds());
        }
    }

    public function findById(string $id): ?Item
    {
        $model = EloquentItem::with('terms')->find($id);

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findAll(PaginationParams $params): PaginatedResult
    {
        $query = EloquentItem::with('terms');
        
        $total = $query->count();
        $lastPage = (int) ceil($total / $params->perPage);
        
        $models = $query
            ->skip(($params->page - 1) * $params->perPage)
            ->take($params->perPage)
            ->get();

        $items = $models->map(fn($model) => $this->toDomain($model))->toArray();

        return new PaginatedResult(
            data: $items,
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

        // Detach all terms before delete
        $model->terms()->detach();
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
            termIds: $model->terms->pluck('id')->toArray(),
        );
    }
}