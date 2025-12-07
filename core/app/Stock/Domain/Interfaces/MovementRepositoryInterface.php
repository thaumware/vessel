<?php

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\ValueObjects\MovementSearchCriteria;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\ValueObjects\MovementType;

interface MovementRepositoryInterface
{
    public function save(Movement $movement): Movement;

    public function findById(string $id): ?Movement;

    /**
     * Búsqueda con criterios múltiples (una sola query).
     * Este es el método preferido para búsquedas complejas.
     * 
     * @return Movement[]
     */
    public function search(MovementSearchCriteria $criteria): array;

    /**
     * Cuenta resultados para los criterios dados.
     */
    public function count(MovementSearchCriteria $criteria): int;

    // === Métodos legacy (usar search() para nuevas implementaciones) ===

    public function findByMovementId(string $movementId): ?Movement;

    public function findBySku(string $sku): array;

    public function findByLocationFrom(string $locationId): array;

    public function findByLocationTo(string $locationId): array;

    public function findByType(MovementType $type): array;

    public function findByStatus(MovementStatus $status): array;

    public function findByReference(string $reference): array;

    public function findByLotId(string $lotId): array;

    public function findByDateRange(\DateTimeInterface $from, \DateTimeInterface $to): array;

    public function delete(string $id): bool;

    /**
     * @return Movement[]
     */
    public function all(): array;
}
