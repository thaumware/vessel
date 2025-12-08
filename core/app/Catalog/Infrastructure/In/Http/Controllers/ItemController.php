<?php

namespace App\Catalog\Infrastructure\In\Http\Controllers;

use App\Catalog\Domain\UseCases\CreateItem;
use App\Catalog\Domain\UseCases\DeleteItem;
use App\Catalog\Domain\UseCases\GetItem;
use App\Catalog\Domain\UseCases\ListItems;
use App\Catalog\Domain\UseCases\UpdateItem;
use App\Shared\Domain\DTOs\PaginationParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Thaumware\Support\Uuid\Uuid;

class ItemController extends Controller
{
    /**
     * GET /items/read
     * Listar todos los items del catálogo
     */
    public function list(
        Request $request,
        ListItems $listItems
    ): JsonResponse {
        $params = PaginationParams::fromRequest($request->query());
        $result = $listItems->execute($params);

        return response()->json($result->toArray());
    }

    /**
     * GET /items/show/{id}
     * Obtener un item específico
     */
    public function show(
        string $id,
        GetItem $getItem
    ): JsonResponse {
        $item = $getItem->execute($id);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json(['data' => $item->toArray()]);
    }

    /**
     * POST /items/create
     * Crear un nuevo item
     * 
     * Solo 'name' es requerido - todo lo demás es opcional
     * Identificadores (SKU, EAN, etc.) se agregan via /items/{id}/identifiers
     */
    public function create(
        Request $request,
        CreateItem $createItem
    ): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'uom_id' => [
                'nullable',
                'string',
                function (string $attribute, $value, $fail) {
                    $exists = DB::table('uom_measures')
                        ->whereNull('deleted_at')
                        ->where(function ($q) use ($value) {
                            $q->where('id', $value)->orWhere('code', $value);
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('The selected ' . $attribute . ' is invalid.');
                    }
                },
            ],
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:active,draft,archived',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'string|uuid',
        ]);

        $resolvedUomId = $this->resolveUomId($validated['uom_id'] ?? null);

        $item = $createItem->execute(
            id: Uuid::v4(),
            name: $validated['name'],
            description: $validated['description'] ?? null,
            uomId: $resolvedUomId,
            notes: $validated['notes'] ?? null,
            status: $validated['status'] ?? 'active',
        );

        return response()->json(['data' => $item->toArray()], 201);
    }

    /**
     * PUT /items/update/{id}
     * Actualizar un item existente
     */
    public function update(
        Request $request,
        string $id,
        UpdateItem $updateItem
    ): JsonResponse {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'uom_id' => [
                'nullable',
                'string',
                function (string $attribute, $value, $fail) {
                    $exists = DB::table('uom_measures')
                        ->whereNull('deleted_at')
                        ->where(function ($q) use ($value) {
                            $q->where('id', $value)->orWhere('code', $value);
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('The selected ' . $attribute . ' is invalid.');
                    }
                },
            ],
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:active,draft,archived',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'string|uuid',
        ]);

        $resolvedUomId = $this->resolveUomId($validated['uom_id'] ?? null);

        $item = $updateItem->execute(
            id: $id,
            name: $validated['name'] ?? null,
            description: $validated['description'] ?? null,
            uomId: $resolvedUomId,
            notes: $validated['notes'] ?? null,
            status: $validated['status'] ?? null,
        );

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json(['data' => $item->toArray()]);
    }

    /**
     * DELETE /items/delete/{id}
     * Eliminar un item
     */
    public function delete(
        string $id,
        DeleteItem $deleteItem
    ): JsonResponse {
        $deleted = $deleteItem->execute($id);

        if (!$deleted) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json(['message' => 'Item deleted'], 200);
    }

        private function resolveUomId(?string $input): ?string
        {
            if (!$input) {
                return null;
            }

            $row = DB::table('uom_measures')
                ->whereNull('deleted_at')
                ->where(function ($q) use ($input) {
                    $q->where('id', $input)->orWhere('code', $input);
                })
                ->first();

            return $row?->id;
        }
}
