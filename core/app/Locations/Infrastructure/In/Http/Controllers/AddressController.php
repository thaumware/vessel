<?php

namespace App\Locations\Infrastructure\In\Http\Controllers;

use App\Locations\Application\UseCases\CreateAddress;
use App\Locations\Application\UseCases\DeleteAddress;
use App\Locations\Application\UseCases\GetAddress;
use App\Locations\Application\UseCases\ListAddresses;
use App\Locations\Application\UseCases\UpdateAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Thaumware\Support\Uuid\Uuid;

class AddressController
{
    public function __construct(
        private CreateAddress $createAddress,
        private GetAddress $getAddress,
        private ListAddresses $listAddresses,
        private UpdateAddress $updateAddress,
        private DeleteAddress $deleteAddress,
    ) {
    }

    /**
     * GET /addresses
     * Listar direcciones con filtros opcionales
     * 
     * Query params:
     *   - parent_id: UUID del padre (para ver hijos directos)
     */
    public function list(Request $request): JsonResponse
    {
        $parentId = $request->query('parent_id');
        $addresses = $this->listAddresses->execute($parentId);

        return response()->json([
            'data' => array_map(fn($address) => $address->toArray(), $addresses)
        ]);
    }

    /**
     * GET /addresses/{id}
     * Obtener una dirección específica
     * 
     * Query params:
     *   - with_children: boolean (incluir hijos)
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $withChildren = $request->boolean('with_children', false);
        $result = $this->getAddress->execute($id, $withChildren);

        if (!$result) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        if ($withChildren && is_array($result)) {
            return response()->json([
                'data' => $result['address']->toArray(),
                'children' => array_map(fn($child) => $child->toArray(), $result['children'] ?? [])
            ]);
        }

        return response()->json(['data' => $result->toArray()]);
    }

    /**
     * POST /addresses
     * Crear una nueva dirección
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address_type' => 'required|string|in:street,city,state,country,postal_code,other',
            'parent_address_id' => 'sometimes|nullable|string|uuid',
            'description' => 'sometimes|nullable|string|max:500',
        ]);

        try {
            $address = $this->createAddress->execute(
                Uuid::v4(),
                $request->all()
            );

            return response()->json(['data' => $address->toArray()], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * PUT /addresses/{id}
     * Actualizar una dirección
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'address_type' => 'sometimes|string|in:street,city,state,country,postal_code,other',
            'parent_address_id' => 'sometimes|nullable|string|uuid',
            'description' => 'sometimes|nullable|string|max:500',
        ]);

        try {
            $address = $this->updateAddress->execute($id, $request->all());

            if (!$address) {
                return response()->json(['error' => 'Address not found'], 404);
            }

            return response()->json(['data' => $address->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /addresses/{id}
     * Eliminar una dirección
     */
    public function delete(string $id): JsonResponse
    {
        try {
            $deleted = $this->deleteAddress->execute($id);

            if (!$deleted) {
                return response()->json(['error' => 'Address not found'], 404);
            }

            return response()->json(['message' => 'Address deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
