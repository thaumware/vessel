<?php

declare(strict_types=1);

namespace App\Stock\Domain;

enum ReservationStatus: string
{
    case ACTIVE = 'active';
    case RELEASED = 'released';
    case EXPIRED = 'expired';
}
