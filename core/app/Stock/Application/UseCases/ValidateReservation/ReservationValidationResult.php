<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ValidateReservation;

/**
 * Resultado de validación de reserva.
 * 
 * Contiene toda la información necesaria para mostrar al usuario
 * si puede o no reservar, y por qué.
 */
class ReservationValidationResult
{
    /**
     * @param array<string> $errors
     * @param array<string> $warnings
     */
    private function __construct(
        public readonly bool $canReserve,
        public readonly float $availableQuantity,
        public readonly float $reservedQuantity,
        public readonly float $totalQuantity,
        public readonly ?float $maxReservationAllowed,
        public readonly array $errors,
        public readonly array $warnings,
        public readonly ?array $itemInfo = null,
        public readonly ?array $locationInfo = null
    ) {
    }

    public static function allowed(
        float $availableQuantity,
        float $reservedQuantity,
        float $totalQuantity,
        ?float $maxReservationAllowed = null,
        array $warnings = [],
        ?array $itemInfo = null,
        ?array $locationInfo = null
    ): self {
        return new self(
            canReserve: true,
            availableQuantity: $availableQuantity,
            reservedQuantity: $reservedQuantity,
            totalQuantity: $totalQuantity,
            maxReservationAllowed: $maxReservationAllowed,
            errors: [],
            warnings: $warnings,
            itemInfo: $itemInfo,
            locationInfo: $locationInfo
        );
    }

    public static function denied(
        string $reason,
        float $availableQuantity = 0,
        float $reservedQuantity = 0,
        float $totalQuantity = 0,
        ?array $itemInfo = null,
        ?array $locationInfo = null
    ): self {
        return new self(
            canReserve: false,
            availableQuantity: $availableQuantity,
            reservedQuantity: $reservedQuantity,
            totalQuantity: $totalQuantity,
            maxReservationAllowed: null,
            errors: [$reason],
            warnings: [],
            itemInfo: $itemInfo,
            locationInfo: $locationInfo
        );
    }

    public function toArray(): array
    {
        return [
            'can_reserve' => $this->canReserve,
            'available_quantity' => $this->availableQuantity,
            'reserved_quantity' => $this->reservedQuantity,
            'total_quantity' => $this->totalQuantity,
            'max_reservation_allowed' => $this->maxReservationAllowed,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'item_info' => $this->itemInfo,
            'location_info' => $this->locationInfo,
        ];
    }
}
