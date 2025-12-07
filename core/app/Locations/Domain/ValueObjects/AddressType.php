<?php

namespace App\Locations\Domain\ValueObjects;

enum AddressType: string
{
    case CITY = 'city';
    case STATE = 'state';
    case COUNTRY = 'country';
    case STREET = 'street';
    case UNIT = 'unit';
    case COMUNA = 'comuna';
    case DEPARTMENT = 'department';
    case FLOOR = 'floor';

    public function canHaveChildren(): bool
    {
        return match ($this) {
            self::UNIT => false,
            default => true,
        };
    }
}
