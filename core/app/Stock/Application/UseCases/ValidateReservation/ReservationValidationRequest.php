<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ValidateReservation;

/**
 * Request para validar si se puede reservar stock.
 * 
 * Valida:
 * - Stock disponible
 * - Configuración de la locación (allow_negative_stock, max_reservation_percentage)
 * - Existencia del item en catálogo
 * - Capacidad de almacenamiento
 */
class ReservationValidationRequest
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $locationId,
        public readonly float $quantity,
        public readonly ?string $lotId = null,
        public readonly ?string $workspaceId = null
    ) {
    }
}
