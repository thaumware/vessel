<?php

namespace App\Locations\Domain\ValueObjects;

enum LocationType: string
{
    case WAREHOUSE = 'warehouse';
    case STORE = 'store';
    case DISTRIBUTION_CENTER = 'distribution_center';
    case OFFICE = 'office';
    case STORAGE_UNIT = 'storage_unit'; // cajón, bin, casillero, etc.

    public function canHaveChildren(): bool
    {
        return match ($this) {
            self::STORAGE_UNIT => false, // los cajones no tienen sub-locaciones
            default => true, // bodegas, tiendas, etc. sí pueden
        };
    }
}
