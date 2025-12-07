<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;

class ListStockItems
{
    public function __construct(
        private StockItemRepositoryInterface $repository,
        private CatalogGatewayInterface $catalogGateway,
    ) {
    }

    /**
     * Listar StockItems con filtros opcionales
     * 
    * @param array $filters Filtros: location_id, item_id (alias sku), catalog_item_id, catalog_origin
     * @param bool $withCatalog Adjuntar datos del catálogo
     * @param int $limit Límite de resultados
     * @param int $offset Offset para paginación
     */
    public function execute(
        array $filters = [],
        bool $withCatalog = false,
        int $limit = 50,
        int $offset = 0
    ): array {
        $stockItems = $this->repository->search($filters, $limit, $offset);

        if ($withCatalog && !empty($stockItems)) {
            $stockItems = $this->catalogGateway->attachCatalogData($stockItems);
        }

        return $stockItems;
    }
}
