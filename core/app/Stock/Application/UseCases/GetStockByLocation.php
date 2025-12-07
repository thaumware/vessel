<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Interfaces\StockRepositoryInterface;

final class GetStockByLocation
{
    public function __construct(private StockRepositoryInterface $stocks)
    {
    }

    /**
     * @return array Array of Stock domain entities
     */
    public function execute(string $locationId): array
    {
        return $this->stocks->getByLocation($locationId);
    }
}
