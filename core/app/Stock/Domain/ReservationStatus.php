<?php

declare(strict_types=1);

namespace App\Stock\Domain;

enum ReservationStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case RELEASED = 'released';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
}
