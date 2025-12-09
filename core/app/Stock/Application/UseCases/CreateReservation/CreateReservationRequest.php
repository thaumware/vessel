<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\CreateReservation;

/**
 * Request para crear una reserva de stock.
 */
class CreateReservationRequest
{
    public function __construct(
        public readonly string $itemId,
        public readonly ?string $locationId,
        public readonly float $quantity,
        public readonly string $referenceType = 'reservation',
        public readonly ?string $referenceId = null,
        public readonly ?string $reason = null,
        public readonly ?string $performedBy = null,
        public readonly ?string $lotId = null,
        public readonly ?array $meta = null,
        public readonly ?string $workspaceId = null,
        public readonly bool $skipValidation = false,
        // Nuevos campos para tracking
        public readonly ?string $reservedBy = null, // user-id, system, etc
        public readonly ?string $expiresAt = null, // ISO 8601 date
        public readonly string $status = 'active', // 'active' | 'pending'
    ) {
    }
}
