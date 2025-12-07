<?php

namespace App\Uom\Infrastructure\Out\InMemory;

use App\Uom\Domain\Entities\Measure;
use App\Uom\Domain\Interfaces\MeasureRepository;

class InMemoryMeasureRepository implements MeasureRepository
{
    /** @var array<string, Measure> */
    private array $measures = [];

    /**
     * Pre-load with base measures from data file.
     */
    public function __construct(bool $loadBaseData = false)
    {
        if ($loadBaseData) {
            $this->loadBaseData();
        }
    }

    public function loadBaseData(): void
    {
        $data = require __DIR__ . '/../Data/measures.php';

        foreach ($data as $item) {
            $measure = new Measure(
                id: $item['id'],
                code: $item['code'],
                name: $item['name'],
                symbol: $item['symbol'] ?? null,
                category: $item['category'] ?? null,
                isBase: $item['is_base'] ?? false,
                description: $item['description'] ?? null,
            );
            $this->measures[$measure->getId()] = $measure;
        }
    }

    public function findById(string $id): ?Measure
    {
        // Search by ID first
        if (isset($this->measures[$id])) {
            return $this->measures[$id];
        }

        // Search by code
        foreach ($this->measures as $measure) {
            if ($measure->getCode() === $id) {
                return $measure;
            }
        }

        return null;
    }

    /**
     * @return Measure[]
     */
    public function findAll(): array
    {
        return array_values($this->measures);
    }

    /**
     * @return Measure[]
     */
    public function findByCategory(string $category): array
    {
        return array_values(array_filter(
            $this->measures,
            fn(Measure $m) => $m->getCategory() === $category
        ));
    }

    /**
     * @return Measure[]
     */
    public function findBaseMeasures(): array
    {
        return array_values(array_filter(
            $this->measures,
            fn(Measure $m) => $m->isBase()
        ));
    }

    public function save($measure): void
    {
        $this->measures[$measure->getId()] = $measure;
    }

    public function update($measure): void
    {
        $this->measures[$measure->getId()] = $measure;
    }

    public function delete(string $id): void
    {
        unset($this->measures[$id]);
    }

    public function clear(): void
    {
        $this->measures = [];
    }
}
