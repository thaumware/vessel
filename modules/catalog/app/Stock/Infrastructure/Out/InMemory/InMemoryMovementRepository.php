<?php

namespace App\Stock\Infrastructure\Out\InMemory;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\ValueObjects\MovementType;

/**
 * InMemory implementation of MovementRepository
 */
class InMemoryMovementRepository implements MovementRepositoryInterface
{
    private array $movements = [];

    public function save(Movement $movement): Movement
    {
        $this->movements[$movement->getId()] = $movement;
        return $movement;
    }

    public function findById(string $id): ?Movement
    {
        return $this->movements[$id] ?? null;
    }

    public function findByMovementId(string $movementId): ?Movement
    {
        // En esta implementación, movementId = id
        return $this->findById($movementId);
    }

    public function findBySku(string $sku): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getSku() === $sku
        ));
    }

    public function findByLocationFrom(string $locationId): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getSourceLocationId() === $locationId 
                || ($m->getType()->removesStock() && $m->getLocationId() === $locationId)
        ));
    }

    public function findByLocationTo(string $locationId): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getDestinationLocationId() === $locationId
                || ($m->getType()->addsStock() && $m->getLocationId() === $locationId)
        ));
    }

    public function findByType(MovementType $type): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getType() === $type
        ));
    }

    public function findByStatus(MovementStatus $status): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getStatus() === $status
        ));
    }

    public function findByReference(string $reference): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getReferenceId() === $reference
        ));
    }

    public function findByLotId(string $lotId): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getLotNumber() === $lotId
        ));
    }

    public function findByDateRange(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getCreatedAt() >= $from && $m->getCreatedAt() <= $to
        ));
    }

    public function delete(string $id): bool
    {
        if (!isset($this->movements[$id])) {
            return false;
        }
        unset($this->movements[$id]);
        return true;
    }

    public function clear(): void
    {
        $this->movements = [];
    }

    public function all(): array
    {
        return array_values($this->movements);
    }
}
