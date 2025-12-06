<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;

class AdjustStockQuantity
{
    public function __construct(
        private StockItemRepositoryInterface $repository,
    ) {
    }

    /**
     * Ajustar cantidad de stock (incrementar o decrementar)
     * 
    * @param string $itemId ID canónico del item (alias sku legado)
     * @param string $locationId ID de la ubicación
     * @param int $delta Cantidad a ajustar (positivo: incrementar, negativo: decrementar)
     * @return StockItem StockItem actualizado
     */
    public function execute(string $itemId, string $locationId, int $delta): StockItem
    {
        return $this->repository->adjustQuantity($itemId, $locationId, $delta);
    }
}
