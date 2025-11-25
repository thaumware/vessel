<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;

class ReleaseStock
{
    public function __construct(
        private StockItemRepositoryInterface $repository,
    ) {
    }

    /**
     * Liberar cantidad de stock reservada
     * 
     * @param string $id ID del StockItem
     * @param int $quantity Cantidad a liberar
     * @return StockItem StockItem actualizado
     * @throws \DomainException Si la cantidad a liberar excede la reservada
     */
    public function execute(string $id, int $quantity): StockItem
    {
        return $this->repository->release($id, $quantity);
    }
}
