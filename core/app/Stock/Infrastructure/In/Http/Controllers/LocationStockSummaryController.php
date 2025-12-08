<?php

namespace App\Stock\Infrastructure\In\Http\Controllers;

use App\Stock\Application\UseCases\GetLocationStockSummary\GetLocationStockSummaryUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * LocationStockSummaryController - Resumen de stock por ubicación
 */
final class LocationStockSummaryController
{
    public function __construct(
        private GetLocationStockSummaryUseCase $useCase
    ) {}

    /**
     * GET /api/stock/locations/{locationId}/summary
     * 
     * Obtiene un resumen del stock en una ubicación y sus hijos,
     * agrupando por itemId y mostrando totales.
     * 
     * Query params:
     * - include_children: bool (default: true) - Incluir ubicaciones hijas
     * 
     * Response:
     * {
     *   "location_id": "uuid",
     *   "includes_children": true,
     *   "total_locations": 3,
     *   "location_ids": ["uuid1", "uuid2", "uuid3"],
     *   "items": [
     *     {
     *       "item_id": "uuid",
     *       "total_quantity": 150.5,
     *       "reserved_quantity": 20.0,
     *       "available_quantity": 130.5,
     *       "locations": ["uuid1", "uuid2"],
     *       "catalog_item": {
     *         "name": "Producto X",
     *         "uom_id": "uuid",
     *         "uom_symbol": "kg"
     *       }
     *     }
     *   ],
     *   "total_items": 5
     * }
     */
    public function summary(string $locationId, Request $request): JsonResponse
    {
        $includeChildren = $request->query('include_children', 'true') === 'true';
        
        $summary = $this->useCase->execute($locationId, $includeChildren);
        
        return response()->json($summary);
    }
}
