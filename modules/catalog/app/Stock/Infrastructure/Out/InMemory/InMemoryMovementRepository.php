<?php

namespace App\Stock\Infrastructure\Out\InMemory;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;

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
        foreach ($this->movements as $movement) {
            if ($movement->getMovementId() === $movementId) {
                return $movement;
            }
        }
        return null;
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
            fn(Movement $m) => $m->getLocationFromId() === $locationId
        ));
    }

    public function findByLocationTo(string $locationId): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getLocationToId() === $locationId
        ));
    }

    public function findByType(string $type): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getMovementType() === $type
        ));
    }

    public function findByReference(string $reference): array
    {
        return array_values(array_filter(
            $this->movements,
            fn(Movement $m) => $m->getReference() === $reference
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
