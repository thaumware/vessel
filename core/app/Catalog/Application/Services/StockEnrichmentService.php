<?php

namespace App\Catalog\Application\Services;

use Illuminate\Support\Facades\DB;

/**
 * Servicio para enriquecer items del catálogo con información de stock agregada.
 */
class StockEnrichmentService
{
    /**
     * Enriquece items con información de stock agregada.
     * 
     * @param array $items Array de items del catálogo
     * @return array Items enriquecidos con stock_summary
     */
    public function enrichWithStock(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        // Extraer IDs de items
        $itemIds = array_column($items, 'id');

        // Agregar stock por catalog_item_id
        $stockData = DB::table('stock_items')
            ->select([
                'catalog_item_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(reserved_quantity) as reserved_quantity'),
                DB::raw('SUM(quantity - reserved_quantity) as available_quantity'),
                DB::raw('COUNT(DISTINCT location_id) as location_count'),
                DB::raw('GROUP_CONCAT(DISTINCT location_id) as location_ids'),
            ])
            ->whereIn('catalog_item_id', $itemIds)
            ->whereNull('deleted_at')
            ->groupBy('catalog_item_id')
            ->get()
            ->keyBy('catalog_item_id');

        // Obtener nombres de ubicaciones si hay stock
        $locationIds = [];
        foreach ($stockData as $stock) {
            if ($stock->location_ids) {
                $locationIds = array_merge(
                    $locationIds,
                    explode(',', $stock->location_ids)
                );
            }
        }
        $locationIds = array_unique($locationIds);

        $locationNames = [];
        if (!empty($locationIds)) {
            // Intentar obtener nombres de ubicaciones si la tabla existe
            try {
                $locationNames = DB::table('locations')
                    ->whereIn('id', $locationIds)
                    ->whereNull('deleted_at')
                    ->pluck('name', 'id')
                    ->toArray();
            } catch (\Throwable $e) {
                // Si la tabla no existe, usar IDs como nombres
                $locationNames = [];
            }
        }

        // Enriquecer cada item con su stock
        return array_map(function ($item) use ($stockData, $locationNames) {
            $itemId = $item['id'];
            $stock = $stockData->get($itemId);

            if ($stock) {
                $locationIdList = explode(',', $stock->location_ids);
                $locations = array_map(
                    fn($locId) => $locationNames[$locId] ?? $locId,
                    $locationIdList
                );

                $item['stock_summary'] = [
                    'total_quantity' => (int) $stock->total_quantity,
                    'reserved_quantity' => (int) $stock->reserved_quantity,
                    'available_quantity' => (int) $stock->available_quantity,
                    'location_count' => (int) $stock->location_count,
                    'locations' => $locations,
                ];
            } else {
                $item['stock_summary'] = [
                    'total_quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                    'location_count' => 0,
                    'locations' => [],
                ];
            }

            return $item;
        }, $items);
    }

    /**
     * Filtra items por ubicación (tienen stock en esa ubicación).
     * 
     * @param array $items Items del catálogo
     * @param string $locationId ID de la ubicación
     * @return array Items filtrados
     */
    public function filterByLocation(array $items, string $locationId): array
    {
        if (empty($items)) {
            return [];
        }

        $itemIds = array_column($items, 'id');

        // Obtener IDs de items que tienen stock en esa ubicación
        $itemsInLocation = DB::table('stock_items')
            ->whereIn('catalog_item_id', $itemIds)
            ->where('location_id', $locationId)
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('catalog_item_id')
            ->toArray();

        return array_filter($items, fn($item) => in_array($item['id'], $itemsInLocation));
    }

    /**
     * Filtra items con stock bajo (available_quantity <= threshold).
     * 
     * @param array $items Items del catálogo (ya enriquecidos con stock_summary)
     * @param int $maxQuantity Umbral máximo
     * @return array Items filtrados
     */
    public function filterLowStock(array $items, int $maxQuantity): array
    {
        return array_filter($items, function ($item) use ($maxQuantity) {
            return isset($item['stock_summary']) 
                && $item['stock_summary']['available_quantity'] <= $maxQuantity;
        });
    }
}
