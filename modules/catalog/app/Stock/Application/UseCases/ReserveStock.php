<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;

class ReserveStock
{
    public function __construct(
        private StockItemRepositoryInterface $repository,
    ) {
    }

    /**
     * Reservar cantidad de stock
     * 
     * @param string $id ID del StockItem
     * @param int $quantity Cantidad a reservar
     * @return StockItem StockItem actualizado con la reserva
     * @throws \DomainException Si no hay suficiente stock disponible
     */
    public function execute(string $id, int $quantity): StockItem
    {
        return $this->repository->reserve($id, $quantity);
    }
}
