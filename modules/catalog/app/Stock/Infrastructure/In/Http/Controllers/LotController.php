<?php

namespace App\Stock\Infrastructure\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Stock\Domain\Entities\Lot;
use App\Stock\Domain\Interfaces\LotRepositoryInterface;
use App\Stock\Domain\ValueObjects\LotStatus;
use App\Shared\Domain\Interfaces\IdGeneratorInterface;
use DateTimeImmutable;

class LotController extends Controller
{
    public function __construct(
        private LotRepositoryInterface $lotRepository,
        private IdGeneratorInterface $idGenerator
    ) {
    }

    /**
     * Lista lotes con filtros.
     */
    public function list(Request $request): JsonResponse
    {
        $lots = $this->lotRepository->all();

        // Filtrar por SKU
        if ($sku = $request->query('sku')) {
            $lots = $this->lotRepository->findBySku($sku);
        }

        // Filtrar por status
        if ($status = $request->query('status')) {
            $lots = array_filter($lots, fn(Lot $l) => $l->getStatus() === $status);
        }

        // Filtrar por expirados
        if ($request->query('expired') === 'true') {
            $lots = array_filter($lots, fn(Lot $l) => $l->isExpired());
        }

        // Filtrar por próximos a vencer
        if ($days = $request->query('expiring_in_days')) {
            $lots = array_filter($lots, fn(Lot $l) => $l->isExpiringSoon((int) $days));
        }

        return response()->json([
            'data' => array_values(array_map(fn(Lot $l) => $l->toArray(), $lots)),
            'meta' => ['total' => count($lots)],
        ]);
    }

    /**
     * Obtiene un lote por ID.
     */
    public function show(string $id): JsonResponse
    {
        $lot = $this->lotRepository->findById($id);

        if (!$lot) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }

        return response()->json(['data' => $lot->toArray()]);
    }

    /**
     * Busca lote por número de lote.
     */
    public function findByLotNumber(string $lotNumber): JsonResponse
    {
        $lot = $this->lotRepository->findByLotNumber($lotNumber);

        if (!$lot) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }

        return response()->json(['data' => $lot->toArray()]);
    }

    /**
     * Crea un nuevo lote.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string|max:100',
            'lot_number' => 'required|string|max:100',
            'expiration_date' => 'nullable|date',
            'production_date' => 'nullable|date',
            'reception_date' => 'nullable|date',
            'source_type' => 'nullable|string|max:50',
            'source_id' => 'nullable|uuid',
            'supplier_lot_number' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,quarantine,expired,depleted',
            'workspace_id' => 'nullable|uuid',
            'meta' => 'nullable|array',
        ]);

        // Verificar que no exista el lot_number
        $existing = $this->lotRepository->findByLotNumber($validated['lot_number']);
        if ($existing) {
            return response()->json([
                'error' => 'Ya existe un lote con ese número',
                'existing_lot' => $existing->toArray(),
            ], 422);
        }

        // Construir identifiers
        $identifiers = ['lot_number' => $validated['lot_number']];
        if (!empty($validated['supplier_lot_number'])) {
            $identifiers['supplier_lot'] = $validated['supplier_lot_number'];
        }

        // Construir attributes
        $attributes = [];
        if (!empty($validated['expiration_date'])) {
            $attributes['expiration_date'] = $validated['expiration_date'];
        }
        if (!empty($validated['production_date'])) {
            $attributes['production_date'] = $validated['production_date'];
        }
        if (!empty($validated['reception_date'])) {
            $attributes['reception_date'] = $validated['reception_date'];
        } else {
            $attributes['reception_date'] = (new DateTimeImmutable())->format('Y-m-d');
        }

        $lot = new Lot(
            id: $this->idGenerator->generate(),
            itemId: $validated['item_id'],
            status: LotStatus::from($validated['status'] ?? 'active'),
            identifiers: $identifiers,
            attributes: $attributes ?: null,
            sourceType: $validated['source_type'] ?? null,
            sourceId: $validated['source_id'] ?? null,
            workspaceId: $validated['workspace_id'] ?? null,
            meta: $validated['meta'] ?? null
        );

        $saved = $this->lotRepository->save($lot);

        return response()->json([
            'data' => $saved->toArray(),
            'message' => 'Lote creado exitosamente',
        ], 201);
    }

    /**
     * Actualiza un lote.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $lot = $this->lotRepository->findById($id);

        if (!$lot) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }

        $validated = $request->validate([
            'expiration_date' => 'nullable|date',
            'production_date' => 'nullable|date',
            'source_type' => 'nullable|string|max:50',
            'source_id' => 'nullable|uuid',
            'supplier_lot_number' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,quarantine,expired,depleted',
            'meta' => 'nullable|array',
        ]);

        // Actualizar identifiers si supplier_lot cambió
        $identifiers = $lot->getIdentifiers() ?? [];
        if (isset($validated['supplier_lot_number'])) {
            $identifiers['supplier_lot'] = $validated['supplier_lot_number'];
        }

        // Actualizar attributes con fechas
        $attributes = $lot->getAttributes() ?? [];
        if (isset($validated['expiration_date'])) {
            $attributes['expiration_date'] = $validated['expiration_date'];
        }
        if (isset($validated['production_date'])) {
            $attributes['production_date'] = $validated['production_date'];
        }

        // Crear nuevo lote con cambios (inmutabilidad)
        $updated = new Lot(
            id: $lot->getId(),
            itemId: $lot->getItemId(),
            status: isset($validated['status']) 
                ? LotStatus::from($validated['status']) 
                : $lot->getStatus(),
            identifiers: $identifiers ?: null,
            attributes: $attributes ?: null,
            sourceType: $validated['source_type'] ?? $lot->getSourceType(),
            sourceId: $validated['source_id'] ?? $lot->getSourceId(),
            workspaceId: $lot->getWorkspaceId(),
            meta: $validated['meta'] ?? $lot->getMeta(),
            createdAt: $lot->getCreatedAt()
        );

        $saved = $this->lotRepository->save($updated);

        return response()->json([
            'data' => $saved->toArray(),
            'message' => 'Lote actualizado exitosamente',
        ]);
    }

    /**
     * Cambia estado del lote a cuarentena.
     */
    public function quarantine(string $id): JsonResponse
    {
        $lot = $this->lotRepository->findById($id);

        if (!$lot) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }

        $updated = $lot->quarantine();
        $saved = $this->lotRepository->save($updated);

        return response()->json([
            'data' => $saved->toArray(),
            'message' => 'Lote puesto en cuarentena',
        ]);
    }

    /**
     * Activa un lote (quita de cuarentena).
     */
    public function activate(string $id): JsonResponse
    {
        $lot = $this->lotRepository->findById($id);

        if (!$lot) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }

        $updated = $lot->activate();
        $saved = $this->lotRepository->save($updated);

        return response()->json([
            'data' => $saved->toArray(),
            'message' => 'Lote activado',
        ]);
    }

    /**
     * Marca lote como agotado.
     */
    public function deplete(string $id): JsonResponse
    {
        $lot = $this->lotRepository->findById($id);

        if (!$lot) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }

        $updated = $lot->markAsDepleted();
        $saved = $this->lotRepository->save($updated);

        return response()->json([
            'data' => $saved->toArray(),
            'message' => 'Lote marcado como agotado',
        ]);
    }

    /**
     * Lista lotes próximos a vencer.
     */
    public function expiringSoon(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 30);
        $threshold = (new DateTimeImmutable())->modify("+{$days} days");
        $lots = $this->lotRepository->findExpiringBefore($threshold);

        return response()->json([
            'data' => array_map(fn(Lot $l) => $l->toArray(), $lots),
            'meta' => [
                'total' => count($lots),
                'days_threshold' => $days,
            ],
        ]);
    }

    /**
     * Elimina un lote.
     */
    public function delete(string $id): JsonResponse
    {
        $lot = $this->lotRepository->findById($id);

        if (!$lot) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }

        $this->lotRepository->delete($id);

        return response()->json([
            'message' => 'Lote eliminado exitosamente',
        ]);
    }
}
