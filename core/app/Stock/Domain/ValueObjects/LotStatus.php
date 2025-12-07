<?php

declare(strict_types=1);

namespace App\Stock\Domain\ValueObjects;

/**
 * Estado de un lote.
 */
enum LotStatus: string
{
    case ACTIVE = 'active';           // Activo y disponible
    case QUARANTINE = 'quarantine';   // En cuarentena (inspección)
    case EXPIRED = 'expired';         // Vencido
    case DEPLETED = 'depleted';       // Agotado
    case BLOCKED = 'blocked';         // Bloqueado (problema de calidad)

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Activo',
            self::QUARANTINE => 'En cuarentena',
            self::EXPIRED => 'Vencido',
            self::DEPLETED => 'Agotado',
            self::BLOCKED => 'Bloqueado',
        };
    }

    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::EXPIRED,
            self::DEPLETED => true,
            default => false,
        };
    }
}
