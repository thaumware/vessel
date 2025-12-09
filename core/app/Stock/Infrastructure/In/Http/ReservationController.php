<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\In\Http;

use App\Stock\Application\UseCases\ValidateReservation\ValidateReservationUseCase;
use App\Stock\Application\UseCases\ValidateReservation\ReservationValidationRequest;
use App\Stock\Application\UseCases\CreateReservation\CreateReservationUseCase;
use App\Stock\Application\UseCases\CreateReservation\CreateReservationRequest;
use App\Stock\Application\UseCases\ReleaseReservation\ReleaseReservationUseCase;
use App\Stock\Application\UseCases\ReleaseReservation\ReleaseReservationRequest;
use App\Stock\Application\UseCases\ApproveReservation\ApproveReservationUseCase;
use App\Stock\Domain\ReservationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador para gestión de reservas de stock.
 * 
 * Endpoints simples y directos:
 * - POST /validate: Valida si se puede reservar (NO modifica estado)
 * - POST /reserve: Crea reserva
 * - POST /release: Libera reserva
 * - GET /: Lista reservas activas
 * - DELETE /{id}: Cancela reserva por ID
 */
class ReservationController
{
    public function __construct(
        private ValidateReservationUseCase $validateReservation,
        private CreateReservationUseCase $createReservation,
        private ReleaseReservationUseCase $releaseReservation,
        private ApproveReservationUseCase $approveReservation,
        private ReservationRepository $reservationRepository
    ) {
    }

    /**
     * POST /api/v1/stock/reservations/validate
     * 
     * Valida si se puede reservar sin modificar estado.
     * 
     * Body:
     * {
     *   "item_id": "ITEM-001",
     *   "location_id": "WAREHOUSE-MAIN",
     *   "quantity": 10
     * }
     * 
     * Response:
     * {
     *   "can_reserve": true,
     *   "available_quantity": 50,
     *   "reserved_quantity": 20,
     *   "total_quantity": 70,
     *   "max_reservation_allowed": 56, // 80% del total si hay límite
     *   "errors": [],
     *   "warnings": [],
     *   "item_info": {...},
     *   "location_info": {...}
     * }
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string',
            'location_id' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'lot_id' => 'nullable|string',
            'workspace_id' => 'nullable|string',
        ]);

        $result = $this->validateReservation->execute(
            new ReservationValidationRequest(
                itemId: $validated['item_id'],
                locationId: $validated['location_id'],
                quantity: (float) $validated['quantity'],
                lotId: $validated['lot_id'] ?? null,
                workspaceId: $validated['workspace_id'] ?? null
            )
        );

        return response()->json($result->toArray(), $result->canReserve ? 200 : 422);
    }

    /**
     * POST /api/v1/stock/reservations/reserve
     * 
     * Crea una reserva de stock.
     * 
     * Body:
     * {
     *   "item_id": "ITEM-001",
     *   "location_id": "WAREHOUSE-MAIN",
     *   "quantity": 10,
     *   "reference_type": "sales_order",
     *   "reference_id": "SO-2024-001",
     *   "reserved_by": "user-123",       // NUEVO: quién reserva
     *   "expires_at": "2024-12-31T23:59:59Z",  // NUEVO: opcional
     *   "reason": "Reserva para orden de venta",
     *   "performed_by": "user@example.com",
     *   "skip_validation": false  // opcional, default false
     * }
     * 
     * Response:
     * {
     *   "success": true,
     *   "reservation_id": "mov-abc-123",
     *   "new_reserved_quantity": 30,
     *   "new_available_quantity": 40,
     *   "errors": [],
     *   "movement": {...}
     * }
     */
    public function reserve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string',
            'location_id' => 'required_unless:status,pending|nullable|string',
            'quantity' => 'required|numeric|min:0.01',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|string',
            'reserved_by' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'reason' => 'nullable|string',
            'performed_by' => 'nullable|string',
            'lot_id' => 'nullable|string',
            'meta' => 'nullable|array',
            'workspace_id' => 'nullable|string',
            'skip_validation' => 'nullable|boolean',
            'status' => 'nullable|in:active,pending',
        ]);

        $result = $this->createReservation->execute(
            new CreateReservationRequest(
                itemId: $validated['item_id'],
                locationId: $validated['location_id'],
                quantity: (float) $validated['quantity'],
                referenceType: $validated['reference_type'] ?? 'reservation',
                referenceId: $validated['reference_id'] ?? null,
                reason: $validated['reason'] ?? null,
                performedBy: $validated['performed_by'] ?? null,
                lotId: $validated['lot_id'] ?? null,
                meta: $validated['meta'] ?? null,
                workspaceId: $validated['workspace_id'] ?? null,
                skipValidation: $validated['skip_validation'] ?? false,
                reservedBy: $validated['reserved_by'] ?? null,
                expiresAt: $validated['expires_at'] ?? null,
                status: $validated['status'] ?? 'active'
            )
        );

        return response()->json($result->toArray(), $result->success ? 201 : 422);
    }

    /**
     * POST /api/v1/stock/reservations/release
     * 
     * Libera una reserva existente.
     * 
     * Body:
     * {
     *   "item_id": "ITEM-001",
     *   "location_id": "WAREHOUSE-MAIN",
     *   "quantity": 10,
     *   "reservation_id": "res-abc-123",  // NUEVO: ID de reserva
     *   "reference_id": "SO-2024-001",  // opcional, para tracking
     *   "reason": "Orden cancelada"
     * }
     * 
     * Response:
     * {
     *   "success": true,
     *   "new_reserved_quantity": 20,
     *   "new_available_quantity": 50,
     *   "errors": [],
     *   "movement": {...}
     * }
     */
    public function release(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string',
            'location_id' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'reservation_id' => 'nullable|string',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|string',
            'reason' => 'nullable|string',
            'performed_by' => 'nullable|string',
            'lot_id' => 'nullable|string',
            'meta' => 'nullable|array',
            'workspace_id' => 'nullable|string',
        ]);

        $result = $this->releaseReservation->execute(
            new ReleaseReservationRequest(
                itemId: $validated['item_id'],
                locationId: $validated['location_id'],
                quantity: (float) $validated['quantity'],
                referenceType: $validated['reference_type'] ?? 'reservation_release',
                referenceId: $validated['reference_id'] ?? null,
                reason: $validated['reason'] ?? null,
                performedBy: $validated['performed_by'] ?? null,
                lotId: $validated['lot_id'] ?? null,
                meta: $validated['meta'] ?? null,
                workspaceId: $validated['workspace_id'] ?? null,
                reservationId: $validated['reservation_id'] ?? null
            )
        );

        return response()->json($result->toArray(), $result->success ? 200 : 422);
    }

    /**
     * POST /api/v1/stock/reservations/{id}/approve
     */
    public function approve(string $id): JsonResponse
    {
        try {
            $result = $this->approveReservation->execute($id);
            return response()->json($result->toArray(), $result->success ? 200 : 422);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/v1/stock/reservations/{id}/reject
     * Body: { "reason": "Motivo del rechazo (opcional)" }
     */
    public function reject(string $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $reservation = $this->reservationRepository->findById($id);
        if (!$reservation) {
             return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
        }

        try {
            $rejected = $reservation->reject($validated['reason'] ?? null);
            $this->reservationRepository->save($rejected);
             return response()->json(['success' => true, 'message' => 'Reserva rechazada']);
        } catch (\DomainException $e) {
             return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /api/v1/stock/reservations
     * 
     * Lista reservas activas.
     * 
     * Query params:
     * - item_id: Filtrar por item (opcional)
     * - location_id: Filtrar por locación (opcional)
     * - status: active|released|expired (default: active)
     * 
     * Response:
     * {
     *   "data": [
     *     {
     *       "id": "res-abc-123",
     *       "item_id": "ITEM-001",
     *       "location_id": "WAREHOUSE-MAIN",
     *       "quantity": 10,
     *       "reserved_by": "user-123",
     *       "reference_type": "sales_order",
     *       "reference_id": "SO-2024-001",
     *       "status": "active",
     *       "expires_at": "2024-12-31T23:59:59Z",
     *       "created_at": "2024-12-01T10:00:00Z",
     *       "released_at": null
     *     }
     *   ],
     *   "total": 42
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'nullable|string',
            'location_id' => 'nullable|string',
            'status' => 'nullable|in:active,released,expired,pending,rejected',
        ]);

        $status = $validated['status'] ?? 'active';
        $statusEnum = \App\Stock\Domain\ReservationStatus::tryFrom($status);

        if ($statusEnum === \App\Stock\Domain\ReservationStatus::ACTIVE && isset($validated['item_id']) && isset($validated['location_id'])) {
            $reservations = $this->reservationRepository->findActiveByItemAndLocation(
                $validated['item_id'],
                $validated['location_id']
            );
        } else {
            // Si el enum es válido, buscamos por ese estado. Si no (o null), default a ACTIVE
            $searchStatus = $statusEnum ?? \App\Stock\Domain\ReservationStatus::ACTIVE;
            $reservations = $this->reservationRepository->findByStatus($searchStatus);
        }

        return response()->json([
            'data' => array_map(fn($r) => $r->toArray(), $reservations),
            'total' => count($reservations),
        ]);
    }

    /**
     * DELETE /api/v1/stock/reservations/{id}
     * 
     * Cancela una reserva por ID.
     * Libera el stock y elimina el registro.
     * 
     * Response:
     * {
     *   "success": true,
     *   "message": "Reserva cancelada y stock liberado"
     * }
     */
    public function destroy(string $id): JsonResponse
    {
        $reservation = $this->reservationRepository->findById($id);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        if (!$reservation->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'La reserva ya no está activa'
            ], 422);
        }

        // Liberar stock primero
        $result = $this->releaseReservation->execute(
            new ReleaseReservationRequest(
                itemId: $reservation->getItemId(),
                locationId: $reservation->getLocationId(),
                quantity: $reservation->getQuantity(),
                referenceType: 'reservation_cancellation',
                referenceId: $id,
                reason: 'Reserva cancelada manualmente',
                reservationId: $id
            )
        );

        if (!$result->success) {
            return response()->json([
                'success' => false,
                'errors' => $result->errors
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reserva cancelada y stock liberado',
            'new_reserved_quantity' => $result->newReservedQuantity,
            'new_available_quantity' => $result->newAvailableQuantity,
        ]);
    }
}
