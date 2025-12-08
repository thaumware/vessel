<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ReleaseReservation;

/**
 * Request para liberar una reserva existente.
 */
class ReleaseReservationRequest
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $locationId,
        public readonly float $quantity,
        public readonly string $referenceType = 'reservation_release',
        public readonly ?string $referenceId = null,
        public readonly ?string $reason = null,
        public readonly ?string $performedBy = null,
        public readonly ?string $lotId = null,
        public readonly ?array $meta = null,
        public readonly ?string $workspaceId = null,
        // Nuevo: ID de la reserva a liberar (opcional)
        public readonly ?string $reservationId = null,
    ) {
    }
}
