<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Interfaces\StockRepositoryInterface;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\Entities\Movement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ApplyMovement
{
    public function __construct(
        private StockRepositoryInterface $stocks,
        private MovementRepositoryInterface $movements
    ) {
    }

    /**
     * Apply a movement: decrement from origin and increment to destination.
     * Persists kardex movements (one per affected location) within the transaction.
     */
    public function execute(string $sku, ?string $fromLocationId, ?string $toLocationId, int $quantity, ?string $fromLocationType = null, ?string $toLocationType = null, ?string $movementId = null, ?string $movementType = null, ?string $reference = null, ?string $userId = null, ?string $workspaceId = null, ?array $meta = null): void
    {
        DB::transaction(function () use ($sku, $fromLocationId, $toLocationId, $quantity, $fromLocationType, $toLocationType, $movementId, $movementType, $reference, $userId, $workspaceId, $meta) {
            // Process origin (out)
            if ($fromLocationId) {
                $fromStock = $this->stocks->adjustQuantity($sku, $fromLocationId, -1 * $quantity);

                $movement = new Movement(
                    (string) Str::uuid(),
                    $movementId,
                    $sku,
                    $fromLocationId,
                    $fromLocationType,
                    null,
                    null,
                    -1 * $quantity,
                    $fromStock->quantity(),
                    $movementType ?? 'out',
                    $reference,
                    $userId,
                    $workspaceId,
                    $meta,
                    new \DateTimeImmutable(),
                    new \DateTimeImmutable()
                );

                $this->movements->save($movement);
            }

            // Process destination (in)
            if ($toLocationId) {
                $toStock = $this->stocks->adjustQuantity($sku, $toLocationId, $quantity);

                $movement = new Movement(
                    (string) Str::uuid(),
                    $movementId,
                    $sku,
                    null,
                    null,
                    $toLocationId,
                    $toLocationType,
                    $quantity,
                    $toStock->quantity(),
                    $movementType ?? 'in',
                    $reference,
                    $userId,
                    $workspaceId,
                    $meta,
                    new \DateTimeImmutable(),
                    new \DateTimeImmutable()
                );

                $this->movements->save($movement);
            }
        });
    }
}
