<?php

namespace App\Stock\Domain\ValueObjects;

enum InventoryMovementType: string
{
    case INCOMING = 'incoming';
    case OUTGOING = 'outgoing';
    case TRANSFER = 'transfer';
    case RESERVATION = 'reservation';


    public function isPositive(): bool
    {
        return match ($this) {
            self::INCOMING, self::TRANSFER => true,
            self::OUTGOING, self::RESERVATION => false,
        };
    }

    public function isNeutral(): bool
    {
        return $this === self::TRANSFER;
    }

    public function opposite(): InventoryMovementType
    {
        return match ($this) {
            self::INCOMING => self::OUTGOING,
            self::OUTGOING => self::INCOMING,
            self::TRANSFER => self::TRANSFER,
            self::RESERVATION => self::RESERVATION,
        };
    }

    public function isNegative(): bool
    {
        return !$this->isPositive() && !$this->isNeutral();
    }
}