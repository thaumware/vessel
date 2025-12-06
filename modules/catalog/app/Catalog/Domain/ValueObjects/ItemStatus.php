<?php

namespace App\Catalog\Domain\ValueObjects;

/**
 * Enum para el estado del Item
 */
enum ItemStatus: string
{
    case Active = 'active';
    case Draft = 'draft';
    case Archived = 'archived';

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }
}
