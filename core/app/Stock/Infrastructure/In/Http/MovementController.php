<?php

namespace App\Stock\Infrastructure\In\Http;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementSearchCriteria;
use App\Stock\Application\Factories\MovementFactory;
use App\Stock\Application\UseCases\Movements\SearchMovements;
use App\Stock\Application\UseCases\Movements\ShowMovement;
use App\Stock\Application\UseCases\Movements\CreateMovement;
use App\Stock\Infrastructure\Out\Models\Eloquent\MovementModel;

/**
 * Controller REST para movimientos de stock.
 * 
 * Endpoints:
 *   GET  /movements          - search (buscar con filtros)
 *   GET  /movements/{id}     - show (obtener uno)
 *   POST /movements          - create (crear genérico)
 *   POST /movements/receipt  - crear recepción
 *   POST /movements/shipment - crear despacho
 *   etc.
 */
class MovementController extends Controller
{
    public function __construct(
        private SearchMovements $searchMovements,
        private ShowMovement $showMovement,
        private CreateMovement $createMovement,
        private MovementFactory $movementFactory
    ) {
    }

    // ========================================
    // READ Operations
    // ========================================

    /**
     * Listar movimientos (alias de search para compatibilidad REST index).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->search($request);
    }

    /**
     * Buscar movimientos con filtros (una sola query).
     * 
     * GET /movements?item_id=X&location_id=Y&type=receipt&...
     */
    public function search(Request $request): JsonResponse
    {
        $criteria = MovementSearchCriteria::fromArray($request->query());
        $result = $this->searchMovements->execute($criteria);

        return response()->json([
            'data' => array_map(fn(Movement $m) => $m->toArray(), $result['data']),
            'meta' => [
                'total' => $result['total'],
                'offset' => $result['offset'],
                'limit' => $result['limit'],
            ]
        ]);
    }

    /**
     * Obtener un movimiento por ID.
     * 
     * GET /movements/{id}
     */
    public function show(string $id): JsonResponse
    {
        $movement = $this->showMovement->execute($id);

        if (!$movement) {
            return response()->json(['error' => 'Movimiento no encontrado'], 404);
        }

        return response()->json(['data' => $movement->toArray()]);
    }

    // ========================================
    // CREATE Operations
    // ========================================

    /**
     * Crear movimiento genérico.
     * 
     * POST /movements
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:' . MovementModel::MAX_MOVEMENT_TYPE_LENGTH,
            'item_id' => 'required|string|max:255',
            'location_id' => 'required|uuid',
            'quantity' => 'required|numeric|min:0.01',
            'lot_id' => 'nullable|string|max:255',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:' . MovementModel::MAX_REFERENCE_LENGTH,
            'workspace_id' => 'nullable|uuid',
        ]);

        $type = MovementType::tryFrom($validated['type']);
        if (!$type) {
            return response()->json([
                'error' => 'Tipo de movimiento inválido',
                'valid_types' => array_column(MovementType::cases(), 'value'),
            ], 422);
        }

        $movement = $this->movementFactory->create(
            type: $type,
            itemId: $validated['item_id'],
            locationId: $validated['location_id'],
            quantity: (int) $validated['quantity'],
            lotId: $validated['lot_id'] ?? null,
            referenceType: $validated['reference_type'] ?? null,
            referenceId: $validated['reference_id'] ?? null,
            reason: $validated['reason'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null
        );

        return $this->processAndRespond($movement, 'Movimiento procesado');
    }

    /**
     * Alias RESTful para create().
     */
    public function store(Request $request): JsonResponse
    {
        return $this->create($request);
    }

    /**
     * Recepción de mercadería.
     * 
     * POST /movements/receipt
     */
    public function receipt(Request $request): JsonResponse
    {
        $validated = $this->validateBasicMovement($request);

        $movement = $this->movementFactory->createReceipt(
            itemId: $validated['item_id'],
            locationId: $validated['location_id'],
            quantity: (int) $validated['quantity'],
            lotId: $validated['lot_id'] ?? null,
            referenceId: $validated['reference_id'] ?? null,
            reason: $validated['reason'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null
        );

        return $this->processAndRespond($movement, 'Recepción procesada');
    }

    /**
     * Despacho/envío.
     * 
     * POST /movements/shipment
     */
    public function shipment(Request $request): JsonResponse
    {
        $validated = $this->validateBasicMovement($request);

        $movement = $this->movementFactory->createShipment(
            itemId: $validated['item_id'],
            locationId: $validated['location_id'],
            quantity: (int) $validated['quantity'],
            lotId: $validated['lot_id'] ?? null,
            referenceId: $validated['reference_id'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null
        );

        return $this->processAndRespond($movement, 'Despacho procesado');
    }

    /**
     * Ajuste de inventario.
     * 
     * POST /movements/adjustment
     */
    public function adjustment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string|max:255',
            'location_id' => 'required|uuid',
            'delta' => 'required|integer',
            'reason' => 'nullable|string|max:' . MovementModel::MAX_REFERENCE_LENGTH,
            'workspace_id' => 'nullable|uuid',
        ]);

        $movement = $this->movementFactory->createAdjustment(
            itemId: $validated['item_id'],
            locationId: $validated['location_id'],
            delta: $validated['delta'],
            reason: $validated['reason'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null
        );

        return $this->processAndRespond($movement, 'Ajuste procesado');
    }

    /**
     * Reserva de stock.
     * 
     * POST /movements/reserve
     */
    public function reserve(Request $request): JsonResponse
    {
        $validated = $this->validateBasicMovement($request);

        $movement = $this->movementFactory->createReservation(
            itemId: $validated['item_id'],
            locationId: $validated['location_id'],
            quantity: (int) $validated['quantity'],
            referenceId: $validated['reference_id'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null
        );

        return $this->processAndRespond($movement, 'Stock reservado');
    }

    /**
     * Liberación de reserva.
     * 
     * POST /movements/release
     */
    public function release(Request $request): JsonResponse
    {
        $validated = $this->validateBasicMovement($request);

        $movement = $this->movementFactory->createRelease(
            itemId: $validated['item_id'],
            locationId: $validated['location_id'],
            quantity: (int) $validated['quantity'],
            referenceId: $validated['reference_id'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null
        );

        return $this->processAndRespond($movement, 'Reserva liberada');
    }

    /**
     * Validación básica sin procesar (devuelve el payload validado).
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $this->validateBasicMovement($request);

        return response()->json(['data' => $validated]);
    }

    /**
     * Transferencia entre ubicaciones.
     * 
     * POST /movements/transfer
     */
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string|max:100',
            'source_location_id' => 'required|uuid',
            'destination_location_id' => 'required|uuid|different:source_location_id',
            'quantity' => 'required|numeric|min:0.01',
            'lot_id' => 'nullable|string|max:100',
            'workspace_id' => 'nullable|uuid',
        ]);

        // Salida
        $outMovement = $this->movementFactory->createTransferOut(
            itemId: $validated['item_id'],
            sourceLocationId: $validated['source_location_id'],
            destinationLocationId: $validated['destination_location_id'],
            quantity: (int) $validated['quantity'],
            lotId: $validated['lot_id'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null
        );

        $outResult = $this->createMovement->execute($outMovement);

        if (!$outResult->isSuccess()) {
            return response()->json([
                'error' => 'Error en salida de transferencia',
                'errors' => $outResult->getErrors(),
            ], 422);
        }

        // Entrada
        $inMovement = $this->movementFactory->createTransferIn(
            itemId: $validated['item_id'],
            sourceLocationId: $validated['source_location_id'],
            destinationLocationId: $validated['destination_location_id'],
            quantity: (int) $validated['quantity'],
            lotId: $validated['lot_id'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null
        );

        $inResult = $this->createMovement->execute($inMovement);

        if (!$inResult->isSuccess()) {
            // Revertir salida
            $revert = $this->movementFactory->createAdjustment(
                itemId: $validated['item_id'],
                locationId: $validated['source_location_id'],
                delta: (int) $validated['quantity'],
                reason: 'Reversión: error en transferencia',
                workspaceId: $validated['workspace_id'] ?? null
            );
            $this->createMovement->execute($revert);

            return response()->json([
                'error' => 'Error en entrada de transferencia (salida revertida)',
                'errors' => $inResult->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => [
                'transfer_out' => $outResult->toArray(),
                'transfer_in' => $inResult->toArray(),
            ],
            'message' => 'Transferencia procesada',
        ], 201);
    }

    // ========================================
    // Helpers
    // ========================================

    /**
     * Tipos de movimiento disponibles.
     * 
     * GET /movements/types
     */
    public function types(): JsonResponse
    {
        $types = array_map(fn(MovementType $t) => [
            'value' => $t->value,
            'label' => $t->label(),
            'adds_stock' => $t->addsStock(),
            'removes_stock' => $t->removesStock(),
        ], MovementType::cases());

        return response()->json(['data' => $types]);
    }

    private function validateBasicMovement(Request $request): array
    {
        return $request->validate([
            'item_id' => 'required|string|max:255',
            'location_id' => 'required|uuid',
            'quantity' => 'required|numeric|min:0.01',
            'lot_id' => 'nullable|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:' . MovementModel::MAX_REFERENCE_LENGTH,
            'workspace_id' => 'nullable|uuid',
        ]);
    }

    private function processAndRespond(Movement $movement, string $message): JsonResponse
    {
        $result = $this->createMovement->execute($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al procesar',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => $message,
        ], 201);
    }
}
