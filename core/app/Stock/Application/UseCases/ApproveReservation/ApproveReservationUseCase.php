<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ApproveReservation;

use App\Stock\Application\UseCases\ValidateReservation\ValidateReservationUseCase;
use App\Stock\Application\UseCases\ValidateReservation\ReservationValidationRequest;
use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ReservationRepository;
use App\Stock\Domain\ReservationStatus;
use App\Shared\Domain\Interfaces\IdGeneratorInterface;
use DomainException;

use App\Stock\Application\UseCases\CreateReservation\CreateReservationResult; // Reusing result format

class ApproveReservationUseCase
{
    public function __construct(
        private StockMovementService $movementService,
        private ValidateReservationUseCase $validateReservation,
        private ReservationRepository $reservationRepository,
        private IdGeneratorInterface $idGenerator
    ) {
    }

    public function execute(string $reservationId): CreateReservationResult
    {
        $reservation = $this->reservationRepository->findById($reservationId);

        if ($reservation === null) {
            throw new DomainException("Reserva no encontrada");
        }

        if ($reservation->getStatus() !== ReservationStatus::PENDING) {
            throw new DomainException("La reserva no está pendiente de aprobación");
        }

        // 1. Validar disponibilidad de stock (Informativo - Admin puede forzar deuda)
        $validation = $this->validateReservation->execute(
            new ReservationValidationRequest(
                itemId: $reservation->getItemId(),
                locationId: $reservation->getLocationId(),
                quantity: $reservation->getQuantity(),
                workspaceId: null 
            )
        );

        // Comentado para permitir "deuda" / stock negativo al aprobar manualmente
        /*
        if (!$validation->canReserve) {
            return CreateReservationResult::failure($validation->errors);
        }
        */

        // 2. Crear movimiento de reserva
        $movement = new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RESERVE,
            itemId: $reservation->getItemId(),
            locationId: $reservation->getLocationId(),
            quantity: $reservation->getQuantity(),
            lotId: null, // Reservation didn't store it
            referenceType: $reservation->getReferenceType(),
            referenceId: $reservation->getReferenceId() ?? $reservationId, 
            reason: 'Aprobación de reserva ' . $reservationId,
            performedBy: $reservation->getReservedBy(), // Or current user?
            workspaceId: null
        );

        // 3. Procesar movimiento (descuenta stock)
        $result = $this->movementService->process($movement);

        if (!$result->isSuccess()) {
             return CreateReservationResult::failure($result->getErrors());
        }

        // 4. Actualizar estado de reserva
        $approvedReservation = $reservation->approve();
        $this->reservationRepository->save($approvedReservation);

        return CreateReservationResult::fromProcessResult($result, $reservationId);
    }
}
