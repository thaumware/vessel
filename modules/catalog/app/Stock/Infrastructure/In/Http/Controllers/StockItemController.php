<?php

namespace App\Stock\Infrastructure\In\Http\Controllers;

use App\Stock\Application\UseCases\CreateStockItem;
use App\Stock\Application\UseCases\GetStockItem;
use App\Stock\Application\UseCases\ListStockItems;
use App\Stock\Application\UseCases\UpdateStockItem;
use App\Stock\Application\UseCases\DeleteStockItem;
use App\Stock\Application\UseCases\AdjustStockQuantity;
use App\Stock\Application\UseCases\ReserveStock;
use App\Stock\Application\UseCases\ReleaseStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Thaumware\Support\Uuid\Uuid;

class StockItemController
{
    public function __construct(
        private CreateStockItem $createStockItem,
        private GetStockItem $getStockItem,
        private ListStockItems $listStockItems,
        private UpdateStockItem $updateStockItem,
        private DeleteStockItem $deleteStockItem,
        private AdjustStockQuantity $adjustStockQuantity,
        private ReserveStock $reserveStock,
        private ReleaseStock $releaseStock,
    ) {
    }

    /**
     * GET /stock/items/list
     * Listar stock con filtros opcionales
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['location_id', 'sku', 'catalog_item_id', 'catalog_origin']);
        $withCatalog = $request->boolean('with_catalog', false);
        $limit = $request->integer('limit', 50);
        $offset = $request->integer('offset', 0);

        $stockItems = $this->listStockItems->execute($filters, $withCatalog, $limit, $offset);

        return response()->json([
            'success' => true,
            'data' => array_map(fn($item) => $item->toArray(), $stockItems),
        ]);
    }

    /**
     * GET /stock/items/show/{id}
     * Obtener un stock item específico
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $withCatalog = $request->boolean('with_catalog', false);
        $stockItem = $this->getStockItem->execute($id, $withCatalog);

        if (!$stockItem) {
            return response()->json([
                'success' => false,
                'message' => 'Stock item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $stockItem->toArray(),
        ]);
    }

    /**
     * POST /stock/items/create
     * Crear un nuevo stock item
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'sku' => 'required|string',
            'catalog_item_id' => 'required|string',
            'location_id' => 'required|string',
            'catalog_origin' => 'sometimes|string',
            'location_type' => 'sometimes|string',
            'quantity' => 'sometimes|integer|min:0',
            'reserved_quantity' => 'sometimes|integer|min:0',
            'lot_number' => 'sometimes|nullable|string',
            'expiration_date' => 'sometimes|nullable|date',
            'serial_number' => 'sometimes|nullable|string',
            'workspace_id' => 'sometimes|nullable|string',
            'meta' => 'sometimes|nullable|array',
        ]);

        try {
            $data = $request->all();
            $data['id'] = Uuid::v4();
            
            $stockItem = $this->createStockItem->execute($data);

            return response()->json([
                'success' => true,
                'data' => $stockItem->toArray(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * PUT /stock/items/update/{id}
     * Actualizar un stock item
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'sku' => 'sometimes|string',
            'catalog_item_id' => 'sometimes|string',
            'location_id' => 'sometimes|string',
            'catalog_origin' => 'sometimes|string',
            'location_type' => 'sometimes|nullable|string',
            'quantity' => 'sometimes|integer|min:0',
            'reserved_quantity' => 'sometimes|integer|min:0',
            'lot_number' => 'sometimes|nullable|string',
            'expiration_date' => 'sometimes|nullable|date',
            'serial_number' => 'sometimes|nullable|string',
            'workspace_id' => 'sometimes|nullable|string',
            'meta' => 'sometimes|nullable|array',
        ]);

        try {
            $stockItem = $this->updateStockItem->execute($id, $request->all());

            if (!$stockItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock item not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $stockItem->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * DELETE /stock/items/delete/{id}
     * Eliminar un stock item
     */
    public function delete(string $id): JsonResponse
    {
        try {
            $deleted = $this->deleteStockItem->execute($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock item not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock item deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /stock/items/adjust
     * Ajustar cantidad de stock
     */
    public function adjust(Request $request): JsonResponse
    {
        $request->validate([
            'sku' => 'required|string',
            'location_id' => 'required|string',
            'delta' => 'required|integer',
        ]);

        try {
            $stockItem = $this->adjustStockQuantity->execute(
                $request->input('sku'),
                $request->input('location_id'),
                $request->input('delta'),
            );

            return response()->json([
                'success' => true,
                'data' => $stockItem->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /stock/items/reserve/{id}
     * Reservar stock
     */
    public function reserve(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $stockItem = $this->reserveStock->execute($id, $request->input('quantity'));

            return response()->json([
                'success' => true,
                'data' => $stockItem->toArray(),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /stock/items/release/{id}
     * Liberar stock reservado
     */
    public function release(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $stockItem = $this->releaseStock->execute($id, $request->input('quantity'));

            return response()->json([
                'success' => true,
                'data' => $stockItem->toArray(),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
