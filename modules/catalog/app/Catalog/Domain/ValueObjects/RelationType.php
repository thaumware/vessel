<?php

namespace App\Catalog\Domain\ValueObjects;

/**
 * Tipos de relación entre items
 */
enum RelationType: string
{
    case Component = 'component';       // Repuesto/parte de
    case Compatible = 'compatible';     // Compatible con
    case Accessory = 'accessory';       // Accesorio de
    case Replacement = 'replacement';   // Reemplaza a
    case VariantOf = 'variant_of';      // Variante de
    case Bundle = 'bundle';             // Parte de kit/bundle
    case Requires = 'requires';         // Requiere
    case Recommended = 'recommended';   // Recomendado con
    case Similar = 'similar';           // Similar a
    case Upgrade = 'upgrade';           // Upgrade de

    /**
     * ¿La relación es bidireccional por naturaleza?
     */
    public function isBidirectional(): bool
    {
        return match ($this) {
            self::Compatible, self::Similar => true,
            default => false,
        };
    }

    /**
     * Obtiene el tipo inverso de la relación
     */
    public function inverse(): ?self
    {
        return match ($this) {
            self::Component => null,        // No tiene inverso directo
            self::Compatible => self::Compatible,
            self::Accessory => null,
            self::Replacement => null,
            self::VariantOf => self::VariantOf,
            self::Bundle => null,
            self::Requires => null,
            self::Recommended => self::Recommended,
            self::Similar => self::Similar,
            self::Upgrade => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Component => 'Componente de',
            self::Compatible => 'Compatible con',
            self::Accessory => 'Accesorio de',
            self::Replacement => 'Reemplaza a',
            self::VariantOf => 'Variante de',
            self::Bundle => 'Parte de bundle',
            self::Requires => 'Requiere',
            self::Recommended => 'Recomendado con',
            self::Similar => 'Similar a',
            self::Upgrade => 'Upgrade de',
        };
    }
}
