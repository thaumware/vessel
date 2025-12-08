<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\ValueObjects\MovementSearchCriteria;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\ValueObjects\MovementType;
use DateTimeImmutable;
use Illuminate\Support\Arr;

class MovementRepository implements MovementRepositoryInterface
{
    public function save(Movement $movement): Movement
    {
        MovementModel::updateOrCreate(
            ['id' => $movement->getId()],
            $this->toPersistence($movement)
        );

        return $movement;
    }

    public function findById(string $id): ?Movement
    {
        $model = MovementModel::find($id);
        return $model ? $this->toDomain($model->toArray()) : null;
    }

    public function search(MovementSearchCriteria $criteria): array
    {
        $query = MovementModel::query();

        if ($criteria->itemId !== null) {
            $query->where('sku', $criteria->itemId);
        }

        if ($criteria->locationId !== null) {
            $query->where(function ($q) use ($criteria) {
                $q->where('location_from_id', $criteria->locationId)
                  ->orWhere('location_to_id', $criteria->locationId);
            });
        }

        if ($criteria->type !== null) {
            $query->where('movement_type', $criteria->type->value);
        }

        if ($criteria->status !== null) {
            $query->where('status', $criteria->status->value);
        }

        if ($criteria->lotId !== null) {
            $query->whereJsonContains('meta->lot_id', $criteria->lotId);
        }

        if ($criteria->referenceType !== null) {
            $query->where('meta->reference_type', $criteria->referenceType);
        }

        if ($criteria->referenceId !== null) {
            $query->where('meta->reference_id', $criteria->referenceId);
        }

        if ($criteria->dateFrom !== null) {
            $query->where('created_at', '>=', $criteria->dateFrom);
        }

        if ($criteria->dateTo !== null) {
            $query->where('created_at', '<=', $criteria->dateTo);
        }

        if ($criteria->workspaceId !== null) {
            $query->where('workspace_id', $criteria->workspaceId);
        }

        $query->orderBy('created_at', $criteria->sortDesc ? 'desc' : 'asc');

        if ($criteria->limit !== null) {
            $query->limit($criteria->limit)->offset($criteria->offset);
        }

        return $query->get()->map(fn($model) => $this->toDomain($model->toArray()))->all();
    }

    public function count(MovementSearchCriteria $criteria): int
    {
        $query = MovementModel::query();
        $this->applyFilters($query, $criteria);
        return $query->count();
    }

    public function findByMovementId(string $movementId): ?Movement
    {
        return $this->findById($movementId);
    }

    public function findBySku(string $sku): array
    {
        return MovementModel::where('sku', $sku)
            ->get()
            ->map(fn($model) => $this->toDomain($model->toArray()))
            ->all();
    }

    public function findByLocationFrom(string $locationId): array
    {
        return MovementModel::where('location_from_id', $locationId)
            ->get()
            ->map(fn($model) => $this->toDomain($model->toArray()))
            ->all();
    }

    public function findByLocationTo(string $locationId): array
    {
        return MovementModel::where('location_to_id', $locationId)
            ->get()
            ->map(fn($model) => $this->toDomain($model->toArray()))
            ->all();
    }

    public function findByType(MovementType $type): array
    {
        return MovementModel::where('movement_type', $type->value)
            ->get()
            ->map(fn($model) => $this->toDomain($model->toArray()))
            ->all();
    }

    public function findByStatus(MovementStatus $status): array
    {
        return MovementModel::where('status', $status->value)
            ->get()
            ->map(fn($model) => $this->toDomain($model->toArray()))
            ->all();
    }

    public function findByReference(string $reference): array
    {
        return MovementModel::where('reference', $reference)
            ->get()
            ->map(fn($model) => $this->toDomain($model->toArray()))
            ->all();
    }

    public function findByLotId(string $lotId): array
    {
        return MovementModel::whereJsonContains('meta->lot_id', $lotId)
            ->get()
            ->map(fn($model) => $this->toDomain($model->toArray()))
            ->all();
    }

    public function findByDateRange(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return MovementModel::whereBetween('created_at', [$from, $to])
            ->get()
            ->map(fn($model) => $this->toDomain($model->toArray()))
            ->all();
    }

    public function delete(string $id): bool
    {
        return (bool) MovementModel::where('id', $id)->delete();
    }

    public function all(): array
    {
        return MovementModel::all()->map(fn($model) => $this->toDomain($model->toArray()))->all();
    }

    // === Private helpers ===

    private function applyFilters($query, MovementSearchCriteria $criteria): void
    {
        if ($criteria->itemId !== null) {
            $query->where('sku', $criteria->itemId);
        }

        if ($criteria->locationId !== null) {
            $query->where(function ($q) use ($criteria) {
                $q->where('location_from_id', $criteria->locationId)
                  ->orWhere('location_to_id', $criteria->locationId);
            });
        }

        if ($criteria->type !== null) {
            $query->where('movement_type', $criteria->type->value);
        }

        if ($criteria->status !== null) {
            $query->where('status', $criteria->status->value);
        }

        if ($criteria->lotId !== null) {
            $query->whereJsonContains('meta->lot_id', $criteria->lotId);
        }

        if ($criteria->referenceType !== null) {
            $query->where('meta->reference_type', $criteria->referenceType);
        }

        if ($criteria->referenceId !== null) {
            $query->where('meta->reference_id', $criteria->referenceId);
        }

        if ($criteria->dateFrom !== null) {
            $query->where('created_at', '>=', $criteria->dateFrom);
        }

        if ($criteria->dateTo !== null) {
            $query->where('created_at', '<=', $criteria->dateTo);
        }

        if ($criteria->workspaceId !== null) {
            $query->where('workspace_id', $criteria->workspaceId);
        }
    }

    private function toPersistence(Movement $movement): array
    {
        $meta = $movement->getMeta() ?? [];
        // Preserve reference info in meta for search; reference column keeps human-readable reason/reference
        $meta = array_merge($meta, array_filter([
            'reference_type' => $movement->getReferenceType(),
            'reference_id' => $movement->getReferenceId(),
            'lot_id' => $movement->getLotId(),
        ]));

        return [
            'sku' => $movement->getItemId(),
            'movement_type' => $movement->getType()->value,
            'status' => $movement->getStatus()->value,
            'location_from_id' => $movement->getSourceLocationId() ?? ($movement->isOutbound() ? $movement->getLocationId() : null),
            'location_to_id' => $movement->getDestinationLocationId() ?? ($movement->isInbound() ? $movement->getLocationId() : null),
            'quantity' => (int) $movement->getQuantity(),
            'balance_after' => null,
            'reference' => $movement->getReason() ?? $movement->getReferenceId(),
            'user_id' => $movement->getPerformedBy(),
            'workspace_id' => $movement->getWorkspaceId(),
            'meta' => $meta,
            'processed_at' => $movement->getProcessedAt()?->format('Y-m-d H:i:s'),
            'created_at' => $movement->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    private function toDomain(array $data): Movement
    {
        $type = MovementType::from($data['movement_type']);
        $status = isset($data['status']) && $data['status'] !== null
            ? MovementStatus::from($data['status'])
            : MovementStatus::PENDING;

        $meta = $data['meta'] ?? [];

        return new Movement(
            id: $data['id'],
            type: $type,
            itemId: $data['sku'],
            locationId: $data['location_to_id'] ?? $data['location_from_id'] ?? $data['location_id'] ?? '',
            quantity: (float) $data['quantity'],
            status: $status,
            lotId: Arr::get($meta, 'lot_id'),
            trackedUnitId: null,
            sourceLocationId: $data['location_from_id'] ?? null,
            destinationLocationId: $data['location_to_id'] ?? null,
            sourceType: null,
            sourceId: null,
            referenceType: Arr::get($meta, 'reference_type'),
            referenceId: Arr::get($meta, 'reference_id') ?? $data['reference'] ?? null,
            reason: $data['reference'] ?? null,
            performedBy: $data['user_id'] ?? null,
            workspaceId: $data['workspace_id'] ?? null,
            meta: $meta,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            processedAt: isset($data['processed_at']) && $data['processed_at'] !== null
                ? new DateTimeImmutable($data['processed_at'])
                : null,
        );
    }
}
