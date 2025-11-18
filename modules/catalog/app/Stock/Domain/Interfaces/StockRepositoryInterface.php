<?php

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\Stock;

interface StockRepositoryInterface
{
    public function getByLocation(string $locationId, ?string $locationType = null): array; // return array of Stock entities

    public function save(Stock $stock): Stock;

    /**
     * Adjust quantity for a SKU at a location by a delta (positive or negative).
     * Returns the resulting Stock entity.
     */
    public function adjustQuantity(string $sku, string $locationId, int $delta, ?string $locationType = null): Stock;
}
