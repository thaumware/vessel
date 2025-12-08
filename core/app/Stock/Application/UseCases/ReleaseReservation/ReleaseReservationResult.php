<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ReleaseReservation;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Services\ProcessMovementResult;

/**
 * Resultado de liberar una reserva.
 */
class ReleaseReservationResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?Movement $movement,
        public readonly ?float $newReservedQuantity,
        public readonly ?float $newAvailableQuantity,
        public readonly array $errors
    ) {
    }

    public static function fromProcessResult(ProcessMovementResult $result): self
    {
        if ($result->isSuccess()) {
            $stockItem = $result->getStockItem();
            return new self(
                success: true,
                movement: $result->getMovement(),
                newReservedQuantity: $stockItem?->getReservedQuantity(),
                newAvailableQuantity: $stockItem?->getAvailableQuantity(),
                errors: []
            );
        }

        return new self(
            success: false,
            movement: null,
            newReservedQuantity: null,
            newAvailableQuantity: null,
            errors: $result->getErrors()
        );
    }

    public static function failure(array $errors): self
    {
        return new self(
            success: false,
            movement: null,
            newReservedQuantity: null,
            newAvailableQuantity: null,
            errors: $errors
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'new_reserved_quantity' => $this->newReservedQuantity,
            'new_available_quantity' => $this->newAvailableQuantity,
            'errors' => $this->errors,
            'movement' => $this->movement?->toArray(),
        ];
    }
}
