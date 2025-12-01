<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Out\InMemory;

use App\Stock\Domain\Entities\Lot;
use App\Stock\Domain\Interfaces\LotRepositoryInterface;
use DateTimeInterface;
use DateTimeImmutable;

/**
 * InMemory implementation of LotRepository
 */
class InMemoryLotRepository implements LotRepositoryInterface
{
    private array $lots = [];

    public function save(Lot $lot): Lot
    {
        $this->lots[$lot->getId()] = $lot;
        return $lot;
    }

    public function findById(string $id): ?Lot
    {
        return $this->lots[$id] ?? null;
    }

    public function findByLotNumber(string $lotNumber): ?Lot
    {
        foreach ($this->lots as $lot) {
            if ($lot->getLotNumber() === $lotNumber) {
                return $lot;
            }
        }
        return null;
    }

    public function findBySku(string $sku): array
    {
        return array_values(array_filter(
            $this->lots,
            fn(Lot $lot) => $lot->getItemId() === $sku
        ));
    }

    public function findBySkuAndLocation(string $sku, string $locationId): array
    {
        // Lots don't have location - this is for consistency with interface
        // In real implementation, this would join with stock_items
        return $this->findBySku($sku);
    }

    public function findExpiringBefore(DateTimeInterface $date): array
    {
        return array_values(array_filter(
            $this->lots,
            fn(Lot $lot) => $lot->getExpirationDate() !== null 
                && $lot->getExpirationDate() <= $date
                && !$lot->isExpired()
        ));
    }

    public function findExpired(): array
    {
        return array_values(array_filter(
            $this->lots,
            fn(Lot $lot) => $lot->isExpired()
        ));
    }

    public function findAvailableBySku(string $sku): array
    {
        $lots = array_filter(
            $this->lots,
            fn(Lot $lot) => $lot->getItemId() === $sku 
                && $lot->isUsable()
        );

        // Sort by expiration date (FEFO)
        usort($lots, function (Lot $a, Lot $b) {
            $aExp = $a->getExpirationDate();
            $bExp = $b->getExpirationDate();
            
            if ($aExp === null && $bExp === null) {
                return 0;
            }
            if ($aExp === null) {
                return 1;
            }
            if ($bExp === null) {
                return -1;
            }
            return $aExp <=> $bExp;
        });

        return array_values($lots);
    }

    public function delete(string $id): bool
    {
        if (!isset($this->lots[$id])) {
            return false;
        }
        unset($this->lots[$id]);
        return true;
    }

    public function all(): array
    {
        return array_values($this->lots);
    }

    public function clear(): void
    {
        $this->lots = [];
    }
}
