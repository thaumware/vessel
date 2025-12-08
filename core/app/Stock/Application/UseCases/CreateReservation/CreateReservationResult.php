<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\CreateReservation;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Services\ProcessMovementResult;

/**
 * Resultado de crear una reserva.
 */
class CreateReservationResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?Movement $movement,
        public readonly ?string $reservationId,
        public readonly ?float $newReservedQuantity,
        public readonly ?float $newAvailableQuantity,
        public readonly array $errors
    ) {
    }

    public static function fromProcessResult(ProcessMovementResult $result, ?string $reservationId = null): self
    {
        if ($result->isSuccess()) {
            $stockItem = $result->getStockItem();
            return new self(
                success: true,
                movement: $result->getMovement(),
                reservationId: $reservationId ?? $result->getMovement()->getId(),
                newReservedQuantity: $stockItem?->getReservedQuantity(),
                newAvailableQuantity: $stockItem?->getAvailableQuantity(),
                errors: []
            );
        }

        return new self(
            success: false,
            movement: null,
            reservationId: null,
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
            reservationId: null,
            newReservedQuantity: null,
            newAvailableQuantity: null,
            errors: $errors
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'reservation_id' => $this->reservationId,
            'new_reserved_quantity' => $this->newReservedQuantity,
            'new_available_quantity' => $this->newAvailableQuantity,
            'errors' => $this->errors,
            'movement' => $this->movement?->toArray(),
        ];
    }
}
