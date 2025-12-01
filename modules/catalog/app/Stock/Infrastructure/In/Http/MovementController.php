<?php

namespace App\Stock\Infrastructure\In\Http;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Application\Factories\MovementFactory;
use DateTimeImmutable;

class MovementController extends Controller
{
    public function __construct(
        private MovementRepositoryInterface $movementRepository,
        private StockMovementService $movementService,
        private MovementFactory $movementFactory
    ) {
    }

    /**
     * Lista movimientos con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $movements = $this->movementRepository->all();

        // Filtrar por tipo
        if ($type = $request->query('type')) {
            $typeEnum = MovementType::tryFrom($type);
            if ($typeEnum) {
                $movements = $this->movementRepository->findByType($typeEnum);
            }
        }

        // Filtrar por SKU
        if ($sku = $request->query('sku')) {
            $movements = $this->movementRepository->findBySku($sku);
        }

        // Filtrar por status
        if ($status = $request->query('status')) {
            $statusEnum = MovementStatus::tryFrom($status);
            if ($statusEnum) {
                $movements = $this->movementRepository->findByStatus($statusEnum);
            }
        }

        return response()->json([
            'data' => array_map(fn(Movement $m) => $m->toArray(), $movements),
            'meta' => [
                'total' => count($movements),
            ]
        ]);
    }

    /**
     * Obtiene un movimiento por ID.
     */
    public function show(string $id): JsonResponse
    {
        $movement = $this->movementRepository->findById($id);

        if (!$movement) {
            return response()->json(['error' => 'Movimiento no encontrado'], 404);
        }

        return response()->json(['data' => $movement->toArray()]);
    }

    /**
     * Crea y procesa un nuevo movimiento.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string|max:100',
            'expiration_date' => 'nullable|date',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|string|max:100',
            'reason' => 'nullable|string|max:500',
            'performed_by' => 'nullable|uuid',
            'workspace_id' => 'nullable|uuid',
            'meta' => 'nullable|array',
        ]);

        // Validar tipo
        $type = MovementType::tryFrom($validated['type']);
        if (!$type) {
            return response()->json([
                'error' => 'Tipo de movimiento inválido',
                'valid_types' => array_column(MovementType::cases(), 'value'),
            ], 422);
        }

        // Crear movimiento
        $movement = new Movement(
            id: $this->generateId(),
            type: $type,
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            quantity: $validated['quantity'],
            status: MovementStatus::PENDING,
            lotNumber: $validated['lot_number'] ?? null,
            expirationDate: isset($validated['expiration_date']) 
                ? new DateTimeImmutable($validated['expiration_date']) 
                : null,
            referenceType: $validated['reference_type'] ?? null,
            referenceId: $validated['reference_id'] ?? null,
            reason: $validated['reason'] ?? null,
            performedBy: $validated['performed_by'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null,
            meta: $validated['meta'] ?? null
        );

        // Procesar movimiento
        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al procesar movimiento',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Movimiento procesado exitosamente',
        ], 201);
    }

    /**
     * Recepción de mercadería (helper).
     */
    public function receipt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string|max:100',
            'expiration_date' => 'nullable|date',
            'reference_id' => 'nullable|string|max:100',
            'reason' => 'nullable|string|max:500',
        ]);

        $movement = $this->movementFactory->createReceipt(
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            quantity: $validated['quantity'],
            lotNumber: $validated['lot_number'] ?? null,
            expirationDate: isset($validated['expiration_date']) 
                ? new DateTimeImmutable($validated['expiration_date']) 
                : null,
            referenceId: $validated['reference_id'] ?? null,
            reason: $validated['reason'] ?? null
        );

        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al procesar recepción',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Recepción procesada exitosamente',
        ], 201);
    }

    /**
     * Despacho/envío (helper).
     */
    public function shipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string|max:100',
            'reference_id' => 'nullable|string|max:100',
        ]);

        $movement = $this->movementFactory->createShipment(
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            quantity: $validated['quantity'],
            lotNumber: $validated['lot_number'] ?? null,
            referenceId: $validated['reference_id'] ?? null
        );

        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al procesar despacho',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Despacho procesado exitosamente',
        ], 201);
    }

    /**
     * Reserva de stock.
     */
    public function reserve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'reference_id' => 'nullable|string|max:100',
        ]);

        $movement = $this->movementFactory->createReservation(
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            quantity: $validated['quantity'],
            referenceId: $validated['reference_id'] ?? null
        );

        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al reservar stock',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Stock reservado exitosamente',
        ], 201);
    }

    /**
     * Libera stock reservado.
     */
    public function release(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'reference_id' => 'nullable|string|max:100',
        ]);

        $movement = $this->movementFactory->createRelease(
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            quantity: $validated['quantity'],
            referenceId: $validated['reference_id'] ?? null
        );

        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al liberar reserva',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Reserva liberada exitosamente',
        ], 201);
    }

    /**
     * Ajuste de inventario.
     */
    public function adjustment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'delta' => 'required|integer',
            'reason' => 'nullable|string|max:500',
        ]);

        $movement = $this->movementFactory->createAdjustment(
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            delta: $validated['delta'],
            reason: $validated['reason'] ?? null
        );

        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al procesar ajuste',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Ajuste procesado exitosamente',
        ], 201);
    }

    /**
     * Transferencia entre ubicaciones.
     */
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'source_location_id' => 'required|uuid',
            'destination_location_id' => 'required|uuid|different:source_location_id',
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string|max:100',
        ]);

        // Crear movimiento de salida
        $movement = $this->movementFactory->createTransferOut(
            sku: $validated['sku'],
            sourceLocationId: $validated['source_location_id'],
            destinationLocationId: $validated['destination_location_id'],
            quantity: $validated['quantity'],
            lotNumber: $validated['lot_number'] ?? null
        );

        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al procesar transferencia',
                'errors' => $result->getErrors(),
            ], 422);
        }

        // TODO: Crear movimiento de entrada en destino

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Transferencia procesada exitosamente',
        ], 201);
    }

    /**
     * Instalación (salida por servicio técnico).
     */
    public function installation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'reference_id' => 'nullable|string|max:100',
            'reason' => 'nullable|string|max:500',
        ]);

        $movement = $this->movementFactory->createInstallation(
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            quantity: $validated['quantity'],
            workOrderId: $validated['reference_id'] ?? null,
            reason: $validated['reason'] ?? null
        );

        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al procesar instalación',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Instalación procesada exitosamente',
        ], 201);
    }

    /**
     * Devolución de cliente (entrada).
     */
    public function customerReturn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'reference_id' => 'nullable|string|max:100',
            'reason' => 'nullable|string|max:500',
        ]);

        $movement = $this->movementFactory->createCustomerReturn(
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            quantity: $validated['quantity'],
            returnOrderId: $validated['reference_id'] ?? null,
            reason: $validated['reason'] ?? null
        );

        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
            return response()->json([
                'error' => 'Error al procesar devolución',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'data' => $result->toArray(),
            'message' => 'Devolución procesada exitosamente',
        ], 201);
    }

    /**
     * Tipos de movimiento disponibles.
     */
    public function types(): JsonResponse
    {
        $types = [];
        foreach (MovementType::cases() as $type) {
            $types[] = [
                'value' => $type->value,
                'label' => $type->label(),
                'adds_stock' => $type->addsStock(),
                'removes_stock' => $type->removesStock(),
                'affects_reservation' => $type->affectsReservation(),
            ];
        }

        return response()->json(['data' => $types]);
    }

    /**
     * Valida un movimiento sin ejecutarlo.
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'sku' => 'required|string|max:100',
            'location_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string|max:100',
            'expiration_date' => 'nullable|date',
        ]);

        $type = MovementType::tryFrom($validated['type']);
        if (!$type) {
            return response()->json([
                'valid' => false,
                'errors' => ['Tipo de movimiento inválido'],
            ]);
        }

        $movement = $this->movementFactory->create(
            type: $type,
            sku: $validated['sku'],
            locationId: $validated['location_id'],
            quantity: $validated['quantity'],
            lotNumber: $validated['lot_number'] ?? null,
            expirationDate: isset($validated['expiration_date']) 
                ? new DateTimeImmutable($validated['expiration_date']) 
                : null
        );

        $validation = $this->movementService->validate($movement);

        return response()->json($validation->toArray());
    }
}
