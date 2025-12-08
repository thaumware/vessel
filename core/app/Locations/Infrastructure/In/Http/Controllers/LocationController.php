<?php

namespace App\Locations\Infrastructure\In\Http\Controllers;

use App\Locations\Application\Dtos\CreateLocationRequest;
use App\Locations\Application\UseCases\CreateLocation;
use App\Locations\Application\UseCases\DeleteLocation;
use App\Locations\Application\UseCases\GetLocation;
use App\Locations\Application\UseCases\ListLocations;
use App\Locations\Application\UseCases\UpdateLocation;
use App\Shared\Domain\DTOs\FilterParams;
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
     * GET /locations/read
     * Listar locaciones con filtros y paginación
     * 
     * Query params:
     *   - page, per_page: paginación
     *   - type: warehouse|store|distribution_center|office|storage_unit
     *   - parent_id: UUID del padre (para ver hijos)
     *   - root: true (solo ubicaciones sin parent_id)
     *   - search: búsqueda por nombre
     */
    public function list(Request $request): JsonResponse
    {
        $params = FilterParams::fromRequest($request->query());
        $result = $this->listLocations->execute($params);

        return response()->json($result->toArray());
    }

    /**
     * GET /locations/show/{id}
     * Obtener una locación específica
     */
    public function show(string $id): JsonResponse
    {
        $location = $this->getLocation->execute($id);

        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        return response()->json(['data' => $location->toArray()]);
    }

    /**
     * POST /locations/create
     * Crear una nueva locación
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'address_id' => 'sometimes|nullable|string|uuid',
            'type' => 'sometimes|string|in:warehouse,store,distribution_center,office,storage_unit',
            'description' => 'sometimes|nullable|string',
            'parent_id' => 'sometimes|nullable|string|uuid',
        ]);

        if ($cycleError = $this->validateParent($request->input('parent_id'))) {
            return response()->json(['error' => $cycleError], 422);
        }

        try {
            $dto = CreateLocationRequest::fromArray($request->all());
            $location = $this->createLocation->execute(Uuid::v4(), $dto->toArray());

            return response()->json(['data' => $location->toArray()], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * PUT /locations/update/{id}
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

        if ($cycleError = $this->validateParent($request->input('parent_id'), $id)) {
            return response()->json(['error' => $cycleError], 422);
        }

        try {
            $location = $this->updateLocation->execute($id, $request->all());

            if (!$location) {
                return response()->json(['error' => 'Location not found'], 404);
            }

            return response()->json(['data' => $location->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /locations/delete/{id}
     * Eliminar una locación
     */
    public function delete(string $id): JsonResponse
    {
        try {
            $this->deleteLocation->execute($id);
            return response()->json(['message' => 'Location deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function validateParent(?string $parentId, ?string $selfId = null): ?string
    {
        if (!$parentId) {
            return null;
        }

        // No puede ser su propio padre
        if ($selfId !== null && $parentId === $selfId) {
            return 'A location cannot be its own parent';
        }

        // Debe existir
        $parent = $this->getLocation->execute($parentId);
        if ($parent === null) {
            return 'Parent location does not exist';
        }

        // Evitar ciclos recorriendo ancestros
        $current = $parent;
        $visited = [];
        while ($current) {
            $id = $current->getId();
            if (in_array($id, $visited, true)) {
                return 'Location hierarchy cycle detected';
            }
            if ($selfId !== null && $id === $selfId) {
                return 'Cycle detected: the selected parent is a descendant of this location';
            }
            $visited[] = $id;
            $parentId = $current->getParentId();
            $current = $parentId ? $this->getLocation->execute($parentId) : null;
        }

        return null;
    }
}
