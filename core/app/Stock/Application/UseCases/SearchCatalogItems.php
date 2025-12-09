<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;

/**
 * Buscar items en el catálogo con información de stock disponible
 */
class SearchCatalogItems
{
    public function __construct(
        private CatalogGatewayInterface $catalogGateway,
        private StockItemRepositoryInterface $stockItemRepository,
    ) {
    }

    /**
     * Buscar items del catálogo y enriquecer con información de stock.
     * 
     * @param string $searchTerm Término de búsqueda
     * @param int $limit Máximo de resultados
     * @return array Items del catálogo con información de stock agregada
     */
    public function execute(string $searchTerm, int $limit = 50): array
    {
        // Buscar en catálogo
        $catalogItems = $this->catalogGateway->searchItems($searchTerm, $limit);

        // Enriquecer cada item con información de stock disponible
        return array_map(function ($catalogItem) {
            $itemId = $catalogItem['id'];
            
            // Buscar stock items para este catalog item
            $stockItems = $this->stockItemRepository->search([
                'catalog_item_id' => $itemId,
            ]);

            // Calcular cantidad total disponible
            $totalQuantity = 0;
            $totalAvailable = 0;
            $locations = [];

            foreach ($stockItems as $stockItem) {
                $totalQuantity += $stockItem->getQuantity();
                $totalAvailable += $stockItem->getAvailableQuantity();
                
                $locationId = $stockItem->getLocationId();
                if (!isset($locations[$locationId])) {
                    $locations[$locationId] = [
                        'location_id' => $locationId,
                        'quantity' => 0,
                        'available' => 0,
                    ];
                }
                
                $locations[$locationId]['quantity'] += $stockItem->getQuantity();
                $locations[$locationId]['available'] += $stockItem->getAvailableQuantity();
            }

            return array_merge($catalogItem, [
                'stock' => [
                    'total_quantity' => $totalQuantity,
                    'available_quantity' => $totalAvailable,
                    'reserved_quantity' => $totalQuantity - $totalAvailable,
                    'locations' => array_values($locations),
                ],
            ]);
        }, $catalogItems);
    }
}
