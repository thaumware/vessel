<?php

declare(strict_types=1);

namespace App\Stock\Domain\ValueObjects;

/**
 * Estado de un movimiento.
 */
enum MovementStatus: string
{
    case PENDING = 'pending';       // Pendiente de procesar
    case COMPLETED = 'completed';   // Procesado exitosamente
    case CANCELLED = 'cancelled';   // Cancelado
    case FAILED = 'failed';         // Falló (ej: sin stock)

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function canProcess(): bool
    {
        return $this === self::PENDING;
    }

    public function canCancel(): bool
    {
        return $this === self::PENDING;
    }
}
