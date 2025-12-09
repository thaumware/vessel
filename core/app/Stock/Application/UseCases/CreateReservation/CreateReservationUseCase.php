<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\CreateReservation;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Application\UseCases\ValidateReservation\ValidateReservationUseCase;
use App\Stock\Application\UseCases\ValidateReservation\ReservationValidationRequest;
use App\Stock\Domain\Reservation;
use App\Stock\Domain\ReservationRepository;
use App\Stock\Domain\ReservationStatus;
use App\Shared\Domain\Interfaces\IdGeneratorInterface;
use DateTimeImmutable;

/**
 * Crea una reserva de stock.
 * 
 * Opcionalmente valida antes de reservar (recomendado).
 */
class CreateReservationUseCase
{
    public function __construct(
        private StockMovementService $movementService,
        private ValidateReservationUseCase $validateReservation,
        private ReservationRepository $reservationRepository,
        private IdGeneratorInterface $idGenerator
    ) {
    }

    public function execute(CreateReservationRequest $request): CreateReservationResult
    {
        $status = ReservationStatus::tryFrom($request->status) ?? ReservationStatus::ACTIVE;
        $reservationId = $this->idGenerator->generate();

        // 0. Si es PENDING, crear solo la entidad Reservation (flujo aprobación)
        if ($status === ReservationStatus::PENDING) {
            $reservation = Reservation::create(
                id: $reservationId,
                itemId: $request->itemId,
                locationId: $request->locationId,
                quantity: $request->quantity,
                reservedBy: $request->reservedBy ?? $request->performedBy ?? 'system',
                referenceType: $request->referenceType,
                referenceId: $request->referenceId ?? $reservationId,
                expiresAt: $request->expiresAt ? new DateTimeImmutable($request->expiresAt) : null,
                status: ReservationStatus::PENDING
            );
            $this->reservationRepository->save($reservation);

            return CreateReservationResult::pending($reservationId);
        }

        // 1. Validar ANTES de reservar (si no se skipea)
        if (!$request->skipValidation) {
            $validation = $this->validateReservation->execute(
                new ReservationValidationRequest(
                    itemId: $request->itemId,
                    locationId: $request->locationId,
                    quantity: $request->quantity,
                    lotId: $request->lotId,
                    workspaceId: $request->workspaceId
                )
            );

            if (!$validation->canReserve) {
                return CreateReservationResult::failure($validation->errors);
            }
        }

        // 2. Crear movimiento de reserva
        $movement = new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RESERVE,
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

        // 4. Guardar Reservation para tracking del frontend
        $reservation = Reservation::create(
            id: $reservationId,
            itemId: $request->itemId,
            locationId: $request->locationId,
            quantity: $request->quantity,
            reservedBy: $request->reservedBy ?? $request->performedBy ?? 'system',
            referenceType: $request->referenceType,
            referenceId: $request->referenceId ?? $reservationId, // fallback al ID de reserva
            expiresAt: $request->expiresAt ? new DateTimeImmutable($request->expiresAt) : null,
            status: ReservationStatus::ACTIVE
        );
        $this->reservationRepository->save($reservation);

        return CreateReservationResult::fromProcessResult($result, $reservationId);
    }
}
