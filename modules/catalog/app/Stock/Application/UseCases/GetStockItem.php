<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;

class GetStockItem
{
    public function __construct(
        private StockItemRepositoryInterface $repository,
        private CatalogGatewayInterface $catalogGateway,
    ) {
    }

    /**
     * Obtener StockItem por ID con datos del catálogo
     */
    public function execute(string $id, bool $withCatalog = false): ?StockItem
    {
        $stockItem = $this->repository->findById($id);

        if ($stockItem && $withCatalog) {
            $enriched = $this->catalogGateway->attachCatalogData([$stockItem]);
            return $enriched[0] ?? $stockItem;
        }

        return $stockItem;
    }
}
