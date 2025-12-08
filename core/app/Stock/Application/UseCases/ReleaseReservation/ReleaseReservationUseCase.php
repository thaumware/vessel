<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ReleaseReservation;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\ReservationRepository;
use App\Shared\Domain\Interfaces\IdGeneratorInterface;
use App\Stock\Domain\Services\ProcessMovementResult;

/**
 * Libera una reserva de stock.
 * 
 * Valida que haya suficiente cantidad reservada antes de liberar.
 */
class ReleaseReservationUseCase
{
    public function __construct(
        private StockMovementService $movementService,
        private StockItemRepositoryInterface $stockRepository,
        private ReservationRepository $reservationRepository,
        private IdGeneratorInterface $idGenerator
    ) {
    }

    public function execute(ReleaseReservationRequest $request): ReleaseReservationResult
    {
        // 1. Validar que existe stock reservado suficiente
        $stockItem = $this->stockRepository->findByItemAndLocation(
            $request->itemId,
            $request->locationId
        );

        if ($stockItem === null) {
            return ReleaseReservationResult::failure([
                "No hay stock del item en esta locación"
            ]);
        }

        if ($stockItem->getReservedQuantity() < $request->quantity) {
            return ReleaseReservationResult::failure([
                "No hay suficiente cantidad reservada para liberar. Reservado: {$stockItem->getReservedQuantity()}, intentando liberar: {$request->quantity}"
            ]);
        }

        // 2. Crear movimiento de liberación
        $movement = new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RELEASE,
            itemId: $request->itemId,
            locationId: $request->locationId,
            quantity: $request->quantity,
            lotId: $request->lotId,
            referenceType: $request->referenceType,
            referenceId: $request->referenceId,
            reason: $request->reason,
            performedBy: $request->performedBy,
            workspaceId: $request->workspaceId,
            meta: $request->meta
        );

        // 3. Procesar
        $result = $this->movementService->process($movement);

        // 4. Marcar reserva como released (si se proporcionó ID)
        if ($request->reservationId) {
            $reservation = $this->reservationRepository->findById($request->reservationId);
            if ($reservation && $reservation->isActive()) {
                $this->reservationRepository->save($reservation->release());
            }
        }

        return ReleaseReservationResult::fromProcessResult($result);
    }
}
