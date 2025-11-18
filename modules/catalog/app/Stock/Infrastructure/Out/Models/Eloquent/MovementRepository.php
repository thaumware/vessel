<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;

class MovementRepository implements MovementRepositoryInterface
{
    public function save(Movement $movement): Movement
    {
        if ($movement->movementId()) {
            $model = MovementModel::updateOrCreate(
                ['movement_id' => $movement->movementId()],
                [
                    'id' => $movement->id(),
                    'sku' => $movement->sku(),
                    'location_from_id' => $movement->locationFromId(),
                    'location_from_type' => $movement->locationFromType(),
                    'location_to_id' => $movement->locationToId(),
                    'location_to_type' => $movement->locationToType(),
                    'quantity' => $movement->quantity(),
                    'balance_after' => $movement->balanceAfter(),
                    'movement_type' => $movement->movementType(),
                    'reference' => $movement->reference(),
                    'user_id' => $movement->userId(),
                    'workspace_id' => $movement->workspaceId(),
                    'meta' => $movement->meta(),
                    'created_at' => $movement->createdAt()?->format('Y-m-d H:i:s'),
                    'processed_at' => $movement->processedAt()?->format('Y-m-d H:i:s'),
                ]
            );
        } else {
            $model = MovementModel::create([
                'id' => $movement->id(),
                'movement_id' => null,
                'sku' => $movement->sku(),
                'location_from_id' => $movement->locationFromId(),
                'location_from_type' => $movement->locationFromType(),
                'location_to_id' => $movement->locationToId(),
                'location_to_type' => $movement->locationToType(),
                'quantity' => $movement->quantity(),
                'balance_after' => $movement->balanceAfter(),
                'movement_type' => $movement->movementType(),
                'reference' => $movement->reference(),
                'user_id' => $movement->userId(),
                'workspace_id' => $movement->workspaceId(),
                'meta' => $movement->meta(),
                'created_at' => $movement->createdAt()?->format('Y-m-d H:i:s'),
                'processed_at' => $movement->processedAt()?->format('Y-m-d H:i:s'),
            ]);
        }

        return new Movement(
            $model->id,
            $model->movement_id,
            $model->sku,
            $model->location_from_id,
            $model->location_from_type,
            $model->location_to_id,
            $model->location_to_type,
            (int)$model->quantity,
            $model->balance_after !== null ? (int)$model->balance_after : null,
            $model->movement_type,
            $model->reference,
            $model->user_id,
            $model->workspace_id,
            $model->meta ? json_decode($model->meta, true) : null,
            $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
            $model->processed_at ? new \DateTimeImmutable($model->processed_at) : null
        );
    }

    public function findByMovementId(string $movementId): ?Movement
    {
        $model = MovementModel::where('movement_id', $movementId)->first();
        if (!$model) {
            return null;
        }
        return new Movement(
            $model->id,
            $model->movement_id,
            $model->sku,
            $model->location_from_id,
            $model->location_from_type,
            $model->location_to_id,
            $model->location_to_type,
            (int) $model->quantity,
            $model->balance_after !== null ? (int) $model->balance_after : null,
            $model->movement_type,
            $model->reference,
            $model->user_id,
            $model->workspace_id,
            $model->meta ? json_decode($model->meta, true) : null,
            $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
            $model->processed_at ? new \DateTimeImmutable($model->processed_at) : null
        );
    }
}
