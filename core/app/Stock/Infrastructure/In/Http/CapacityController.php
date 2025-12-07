<?php

namespace App\Stock\Infrastructure\In\Http;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Stock\Application\UseCases\GetLocationSettings\GetLocationSettingsUseCase;
use App\Stock\Application\UseCases\ManageLocationSettings\ManageLocationSettingsUseCase;
use App\Stock\Application\UseCases\ManageLocationSettings\ManageLocationSettingsInput;
use App\Stock\Domain\Services\StockCapacityService;

class CapacityController extends Controller
{
    public function __construct(
        private GetLocationSettingsUseCase $getLocationSettings,
        private ManageLocationSettingsUseCase $manageSettings,
        private StockCapacityService $capacityService
    ) {
    }

    /**
     * Obtiene configuración de capacidad de una ubicación.
     */
    public function show(string $locationId): JsonResponse
    {
        $settings = $this->getLocationSettings->getSettings($locationId);

        if (!$settings) {
            return response()->json([
                'data' => null,
                'message' => 'No hay configuración de capacidad para esta ubicación (sin límites)',
            ]);
        }

        return response()->json(['data' => $settings]);
    }

    /**
     * Crea o actualiza configuración de capacidad.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id' => 'required|uuid',
            'max_quantity' => 'nullable|integer|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:10',
            'allowed_item_types' => 'nullable|array',
            'allowed_item_types.*' => 'string|max:50',
            'allow_mixed_skus' => 'nullable|boolean',
            'allow_mixed_lots' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $input = ManageLocationSettingsInput::fromArray($validated);
        $output = $this->manageSettings->execute($input);

        if (!$output->success) {
            return response()->json([
                'error' => $output->error,
            ], 422);
        }

        return response()->json([
            'data' => $output->settings->toArray(),
            'message' => 'Configuración guardada exitosamente',
        ]);
    }

    /**
     * Elimina configuración de capacidad (vuelve a sin límites).
     */
    public function destroy(string $locationId): JsonResponse
    {
        // Por ahora, desactivar es equivalente a eliminar
        $input = ManageLocationSettingsInput::fromArray([
            'location_id' => $locationId,
            'is_active' => false,
        ]);

        $output = $this->manageSettings->execute($input);

        if (!$output->success) {
            return response()->json(['error' => $output->error], 422);
        }

        return response()->json([
            'message' => 'Configuración de capacidad desactivada',
        ]);
    }

    /**
     * Verifica si una ubicación puede recibir cierta cantidad.
     */
    public function canAccept(Request $request, string $locationId): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'item_type' => 'nullable|string|max:50',
        ]);

        $result = $this->capacityService->canAcceptStock(
            locationId: $locationId,
            quantity: $validated['quantity'],
            itemId: $validated['item_id'],
            itemType: $validated['item_type'] ?? null
        );

        return response()->json($result->toArray());
    }

    /**
     * Obtiene estadísticas de capacidad de una ubicación.
     */
    public function stats(string $locationId): JsonResponse
    {
        $stats = $this->capacityService->getCapacityStats($locationId);

        return response()->json(['data' => $stats]);
    }

    /**
     * Capacidad disponible en una ubicación.
     */
    public function available(string $locationId): JsonResponse
    {
        $available = $this->capacityService->getAvailableCapacity($locationId);

        return response()->json([
            'data' => [
                'location_id' => $locationId,
                'available_capacity' => $available,
                'has_limit' => $available !== null,
            ]
        ]);
    }

    /**
     * Stock total en un árbol de ubicaciones.
     */
    public function totalStock(string $locationId): JsonResponse
    {
        $total = $this->capacityService->getTotalStockForLocationTree($locationId);

        return response()->json([
            'data' => [
                'location_id' => $locationId,
                'total_quantity' => $total,
            ]
        ]);
    }

    /**
     * Item IDs únicos en una ubicación.
     */
    public function uniqueItemIds(string $locationId): JsonResponse
    {
        $itemIds = $this->capacityService->getUniqueItemIds($locationId);

        return response()->json([
            'data' => [
                'location_id' => $locationId,
                'item_ids' => $itemIds,
                'count' => count($itemIds),
            ]
        ]);
    }

    /**
     * Verifica si una ubicación está llena.
     */
    public function isFull(string $locationId): JsonResponse
    {
        $isFull = $this->capacityService->isLocationFull($locationId);

        return response()->json([
            'data' => [
                'location_id' => $locationId,
                'is_full' => $isFull,
            ]
        ]);
    }
}
