<?php

declare(strict_types=1);

namespace App\Stock\Domain\ValueObjects;

/**
 * Estado de una unidad rastreada (TrackedUnit).
 */
enum TrackingStatus: string
{
    case AVAILABLE = 'available';       // Disponible en inventario
    case RESERVED = 'reserved';         // Reservada para una orden
    case ASSIGNED = 'assigned';         // Asignada a cliente/empleado/proyecto
    case IN_TRANSIT = 'in_transit';     // En tránsito
    case IN_SERVICE = 'in_service';     // En servicio técnico
    case DAMAGED = 'damaged';           // Dañada
    case LOST = 'lost';                 // Perdida
    case DISPOSED = 'disposed';         // Desechada/dada de baja
    case SOLD = 'sold';                 // Vendida

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponible',
            self::RESERVED => 'Reservada',
            self::ASSIGNED => 'Asignada',
            self::IN_TRANSIT => 'En tránsito',
            self::IN_SERVICE => 'En servicio',
            self::DAMAGED => 'Dañada',
            self::LOST => 'Perdida',
            self::DISPOSED => 'Dada de baja',
            self::SOLD => 'Vendida',
        };
    }

    public function isUsable(): bool
    {
        return match ($this) {
            self::AVAILABLE,
            self::RESERVED => true,
            default => false,
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::DISPOSED,
            self::LOST,
            self::SOLD => true,
            default => false,
        };
    }
}
