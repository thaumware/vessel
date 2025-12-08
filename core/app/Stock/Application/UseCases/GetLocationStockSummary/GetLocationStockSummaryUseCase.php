<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\GetLocationStockSummary;

use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;

/**
 * Suma cantidades de stock en una ubicación y sus hijos,
 * agrupando por itemId y UoM.
 * 
 * IMPORTANTE: NO suma cantidades de diferentes UoMs (kg ≠ litros ≠ unidades).
 * Cada combinación itemId + UoM se reporta por separado.
 * 
 * Enriquece con datos del catálogo via CatalogGateway (que usa Portal).
 */
class GetLocationStockSummaryUseCase
{
    public function __construct(
        private StockItemRepositoryInterface $stockRepository,
        private LocationGatewayInterface $locationGateway,
        private CatalogGatewayInterface $catalogGateway,
    ) {}

    /**
     * Obtiene resumen de stock por itemId y UoM para una ubicación y sus hijos.
     * 
     * Agrupa por itemId + UoM del catálogo. Si un item tiene stock en diferentes
     * UoMs, aparecerá múltiples veces en el resultado.
     * 
     * @param string $locationId ID de la ubicación raíz
     * @param bool $includeChildren Si incluir ubicaciones hijas (default: true)
     * @return array Resumen de stock agrupado por item + UoM
     */
    public function execute(string $locationId, bool $includeChildren = true): array
    {
        // Obtener ubicación + hijos si se solicita
        $allLocationIds = [$locationId];
        if ($includeChildren) {
            $descendantIds = $this->locationGateway->getDescendantIds($locationId);
            $allLocationIds = array_merge($allLocationIds, $descendantIds);
        }

        // Obtener todo el stock de estas ubicaciones
        $stockItems = [];
        foreach ($allLocationIds as $locId) {
            $items = $this->stockRepository->findByLocation($locId);
            $stockItems = array_merge($stockItems, $items);
        }

        // Primero enriquecer con catálogo para obtener UoM de cada item
        $representativeItems = [];
        foreach ($stockItems as $stock) {
            $itemId = $stock->getItemId();
            if (!isset($representativeItems[$itemId])) {
                $representativeItems[$itemId] = $stock;
            }
        }

        $itemUomMap = []; // itemId => uom_id
        if (!empty($representativeItems)) {
            $enriched = $this->catalogGateway->attachCatalogData(array_values($representativeItems));
            
            foreach ($enriched as $enrichedData) {
                $itemId = $enrichedData['item_id'] ?? null;
                if ($itemId) {
                    $catalogData = $enrichedData['catalog_item'] ?? null;
                    $itemUomMap[$itemId] = [
                        'uom_id' => $catalogData['uom_id'] ?? 'unknown',
                        'uom_symbol' => $catalogData['uom_symbol'] ?? 'unidad',
                        'name' => $catalogData['name'] ?? null,
                    ];
                }
            }
        }

        // Agrupar por itemId + UoM (para no mezclar unidades incompatibles)
        $grouped = [];
        foreach ($stockItems as $stock) {
            $itemId = $stock->getItemId();
            $uomId = $itemUomMap[$itemId]['uom_id'] ?? 'unknown';
            $key = $itemId . '::' . $uomId; // Clave compuesta
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'item_id' => $itemId,
                    'uom_id' => $uomId,
                    'uom_symbol' => $itemUomMap[$itemId]['uom_symbol'] ?? 'unidad',
                    'total_quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                    'locations' => [],
                    'catalog_item' => $itemUomMap[$itemId] ?? null,
                ];
            }
            
            $grouped[$key]['total_quantity'] += $stock->getQuantity();
            $grouped[$key]['reserved_quantity'] += $stock->getReservedQuantity();
            $grouped[$key]['available_quantity'] += $stock->getAvailableQuantity();
            
            // Registrar ubicaciones donde está
            if (!in_array($stock->getLocationId(), $grouped[$key]['locations'])) {
                $grouped[$key]['locations'][] = $stock->getLocationId();
            }
        }

        return [
            'location_id' => $locationId,
            'includes_children' => $includeChildren,
            'total_locations' => count($allLocationIds),
            'location_ids' => $allLocationIds,
            'items' => array_values($grouped),
            'total_items' => count($grouped),
        ];
    }
}
