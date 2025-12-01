<?php

namespace App\Stock\Infrastructure\Out\InMemory;

use App\Stock\Domain\Entities\Batch;
use App\Stock\Domain\Interfaces\BatchRepositoryInterface;

/**
 * InMemory implementation of BatchRepository
 */
class InMemoryBatchRepository implements BatchRepositoryInterface
{
    private array $batches = [];

    public function save(Batch $batch): Batch
    {
        $this->batches[$batch->id()] = $batch;
        return $batch;
    }

    public function findById(string $id): ?Batch
    {
        return $this->batches[$id] ?? null;
    }

    public function findBySkuAndLocation(string $sku, string $locationId): ?Batch
    {
        foreach ($this->batches as $batch) {
            if ($batch->sku() === $sku && $batch->locationId() === $locationId) {
                return $batch;
            }
        }
        return null;
    }

    public function findBySku(string $sku): array
    {
        return array_values(array_filter(
            $this->batches,
            fn(Batch $batch) => $batch->sku() === $sku
        ));
    }

    public function findByLocation(string $locationId): array
    {
        return array_values(array_filter(
            $this->batches,
            fn(Batch $batch) => $batch->locationId() === $locationId
        ));
    }

    public function findByLotNumber(string $lotNumber): array
    {
        return array_values(array_filter(
            $this->batches,
            fn(Batch $batch) => $batch->lotNumber() === $lotNumber
        ));
    }

    public function delete(string $id): bool
    {
        if (!isset($this->batches[$id])) {
            return false;
        }
        unset($this->batches[$id]);
        return true;
    }

    public function clear(): void
    {
        $this->batches = [];
    }
}
