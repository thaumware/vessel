<?php

namespace App\Uom\Infrastructure\Out\Models;

use App\Uom\Domain\Interfaces\MeasureRepository as MeasureRepositoryInterface;
use App\Uom\Domain\Entities\Measure;

/**
 * Array-backed repository that reads from the data files.
 * This is the default implementation until Eloquent is needed.
 */
class MeasureRepository implements MeasureRepositoryInterface
{
    private array $data;

    public function __construct()
    {
        $this->data = require __DIR__ . '/../Data/measures.php';
    }

    /**
     * @return Measure[]
     */
    public function findAll(): array
    {
        return array_map(fn($item) => $this->toEntity($item), $this->data);
    }

    public function findById(string $id): ?Measure
    {
        foreach ($this->data as $item) {
            if ($item['id'] === $id || $item['code'] === $id) {
                return $this->toEntity($item);
            }
        }
        return null;
    }

    /**
     * @return Measure[]
     */
    public function findByCategory(string $category): array
    {
        $filtered = array_filter($this->data, fn($item) => ($item['category'] ?? null) === $category);
        return array_map(fn($item) => $this->toEntity($item), array_values($filtered));
    }

    /**
     * @return Measure[]
     */
    public function findBaseMeasures(): array
    {
        $filtered = array_filter($this->data, fn($item) => ($item['is_base'] ?? false) === true);
        return array_map(fn($item) => $this->toEntity($item), array_values($filtered));
    }

    public function save($measure): void
    {
        // No-op for file-backed array
    }

    public function update($measure): void
    {
        // No-op for file-backed array
    }

    public function delete(string $id): void
    {
        // No-op for file-backed array
    }

    private function toEntity(array $item): Measure
    {
        return new Measure(
            id: $item['id'],
            code: $item['code'],
            name: $item['name'],
            symbol: $item['symbol'] ?? null,
            category: $item['category'] ?? null,
            isBase: $item['is_base'] ?? false,
            description: $item['description'] ?? null,
        );
    }
}