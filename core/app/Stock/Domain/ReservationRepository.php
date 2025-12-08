<?php

declare(strict_types=1);

namespace App\Stock\Domain;

interface ReservationRepository
{
    /**
     * Guarda una nueva reserva
     */
    public function save(Reservation $reservation): void;

    /**
     * Encuentra una reserva por ID
     */
    public function findById(string $id): ?Reservation;

    /**
     * Encuentra reservas activas por item y locación
     */
    public function findActiveByItemAndLocation(string $itemId, string $locationId): array;

    /**
     * Encuentra todas las reservas activas
     */
    public function findActive(): array;

    /**
     * Encuentra reservas por reference (para liberar por orden completa)
     */
    public function findByReference(string $referenceType, string $referenceId): array;

    /**
     * Marca reservas expiradas (job/cron)
     */
    public function markExpired(): int;

    /**
     * Elimina una reserva por ID
     */
    public function delete(string $id): void;
}
