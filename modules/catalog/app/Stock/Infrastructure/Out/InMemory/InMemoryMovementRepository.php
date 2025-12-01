<?php

namespace App\Stock\Infrastructure\Out\InMemory;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\ValueObjects\MovementSearchCriteria;
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

    /**
     * Búsqueda con criterios múltiples - UNA SOLA PASADA.
     */
    public function search(MovementSearchCriteria $criteria): array
    {
        $results = array_filter($this->movements, function (Movement $m) use ($criteria) {
            // Filtro por itemId
            if ($criteria->itemId !== null && $m->getItemId() !== $criteria->itemId) {
                return false;
            }

            // Filtro por locationId (incluye source y destination)
            if ($criteria->locationId !== null) {
                $matchesLocation = $m->getLocationId() === $criteria->locationId
                    || $m->getSourceLocationId() === $criteria->locationId
                    || $m->getDestinationLocationId() === $criteria->locationId;
                if (!$matchesLocation) {
                    return false;
                }
            }

            // Filtro por type
            if ($criteria->type !== null && $m->getType() !== $criteria->type) {
                return false;
            }

            // Filtro por status
            if ($criteria->status !== null && $m->getStatus() !== $criteria->status) {
                return false;
            }

            // Filtro por lotId
            if ($criteria->lotId !== null && $m->getLotId() !== $criteria->lotId) {
                return false;
            }

            // Filtro por referenceType
            if ($criteria->referenceType !== null && $m->getReferenceType() !== $criteria->referenceType) {
                return false;
            }

            // Filtro por referenceId
            if ($criteria->referenceId !== null && $m->getReferenceId() !== $criteria->referenceId) {
                return false;
            }

            // Filtro por rango de fechas
            if ($criteria->dateFrom !== null && $m->getCreatedAt() < $criteria->dateFrom) {
                return false;
            }
            if ($criteria->dateTo !== null && $m->getCreatedAt() > $criteria->dateTo) {
                return false;
            }

            // Filtro por workspaceId
            if ($criteria->workspaceId !== null && $m->getWorkspaceId() !== $criteria->workspaceId) {
                return false;
            }

            return true;
        });

        // Ordenar
        $results = array_values($results);
        usort($results, function (Movement $a, Movement $b) use ($criteria) {
            $comparison = $b->getCreatedAt() <=> $a->getCreatedAt();
            return $criteria->sortDesc ? $comparison : -$comparison;
        });

        // Aplicar paginación
        if ($criteria->limit !== null) {
            $results = array_slice($results, $criteria->offset, $criteria->limit);
        } elseif ($criteria->offset > 0) {
            $results = array_slice($results, $criteria->offset);
        }

        return $results;
    }

    public function count(MovementSearchCriteria $criteria): int
    {
        // Para contar, usamos search sin paginación
        $criteriaWithoutPagination = new MovementSearchCriteria(
            itemId: $criteria->itemId,
            locationId: $criteria->locationId,
            type: $criteria->type,
            status: $criteria->status,
            lotId: $criteria->lotId,
            referenceType: $criteria->referenceType,
            referenceId: $criteria->referenceId,
            dateFrom: $criteria->dateFrom,
            dateTo: $criteria->dateTo,
            workspaceId: $criteria->workspaceId,
            offset: 0,
            limit: null,
        );
        
        return count($this->search($criteriaWithoutPagination));
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
            fn(Movement $m) => $m->getItemId() === $sku
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
            fn(Movement $m) => $m->getLotId() === $lotId
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
