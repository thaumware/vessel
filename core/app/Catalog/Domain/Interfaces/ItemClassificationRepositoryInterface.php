<?php

namespace App\Catalog\Domain\Interfaces;

use App\Catalog\Domain\Entities\ItemClassification;

interface ItemClassificationRepositoryInterface
{
    /**
     * Reemplaza por completo las clasificaciones de un item.
     * Implementaciones pueden hacer upsert o delete/insert según corresponda.
     */
    public function replaceForItem(string $itemId, array $classifications): void;

    /**
     * Obtiene todas las clasificaciones de un item.
     *
     * @return ItemClassification[]
     */
    public function findByItem(string $itemId): array;
}
