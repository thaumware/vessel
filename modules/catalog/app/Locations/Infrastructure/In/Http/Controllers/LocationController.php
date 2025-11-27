<?php

namespace App\Locations\Infrastructure\In\Http\Controllers;

use App\Locations\Application\Dtos\CreateLocationRequest;
use App\Locations\Application\UseCases\CreateLocation;
use App\Locations\Application\UseCases\DeleteLocation;
use App\Locations\Application\UseCases\GetLocation;
use App\Locations\Application\UseCases\ListLocations;
use App\Locations\Application\UseCases\UpdateLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Thaumware\Support\Uuid\Uuid;

class LocationController
{
    public function __construct(
        private CreateLocation $createLocation,
        private GetLocation $getLocation,
        private ListLocations $listLocations,
        private UpdateLocation $updateLocation,
        private DeleteLocation $deleteLocation,
    ) {
    }

    /**
     * GET /locations/list
     * Listar todas las locaciones
     * 
     * Query params:
     *   - type: warehouse|store|distribution_center|office|storage_unit
     *   - parent_id: UUID del padre (para ver hijos de una ubicación)
     *   - root: 1 (solo ubicaciones raíz, sin parent_id)
     */
    public function list(Request $request): JsonResponse
    {
        $filters = [];
        
        if ($request->has('type')) {
            $filters['type'] = $request->query('type');
        }
        
        if ($request->has('parent_id')) {
            $filters['parent_id'] = $request->query('parent_id');
        }
        
        if ($request->query('root') === '1') {
            $filters['root'] = true;
        }

        $locations = $this->listLocations->execute($filters);

        return response()->json([
            'success' => true,
            'data' => array_map(fn($loc) => $loc->toArray(), $locations),
        ]);
    }

    /**
     * GET /locations/show/:id
     * Obtener una locación específica
     */
    public function show(string $id): JsonResponse
    {
        $location = $this->getLocation->execute($id);

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $location->toArray(),
        ]);
    }

    /**
     * POST /locations/create
     * Crear una nueva locación
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'address_id' => 'required|string',
            'type' => 'sometimes|string|in:warehouse,store,distribution_center,office,storage_unit',
            'description' => 'sometimes|nullable|string',
            'parent_id' => 'sometimes|nullable|string|uuid',
        ]);

        try {
            $dto = CreateLocationRequest::fromArray($request->all());
            $location = $this->createLocation->execute(Uuid::v4(), $dto->toArray());

            return response()->json([
                'success' => true,
                'data' => $location->toArray(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * PUT /locations/update/:id
     * Actualizar una locación
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string',
            'address_id' => 'sometimes|string',
            'type' => 'sometimes|string|in:warehouse,store,distribution_center,office,storage_unit',
            'description' => 'sometimes|nullable|string',
            'parent_id' => 'sometimes|nullable|string|uuid',
        ]);

        try {
            $location = $this->updateLocation->execute($id, $request->all());

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $location->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * DELETE /locations/delete/:id
     * Eliminar una locación
     */
    public function delete(string $id): JsonResponse
    {
        try {
            $this->deleteLocation->execute($id);

            return response()->json([
                'success' => true,
                'message' => 'Location deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
