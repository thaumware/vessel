<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;

class DeleteStockItem
{
    public function __construct(
        private StockItemRepositoryInterface $repository,
    ) {
    }

    /**
     * Eliminar un StockItem
     */
    public function execute(string $id): void
    {
        $this->repository->delete($id);
    }
}
