<?php

namespace App\Uom\Infrastructure\Out\Models;

use App\Uom\Domain\Interfaces\MeasureRepository as MeasureRepositoryInterface;
use App\Uom\Domain\Entities\Measure;

class ArrayMeasureRepository implements MeasureRepositoryInterface
{
    /**
     * Read all measures from a local PHP array (temporary persistence).
     * This adapter can be swapped later for an SQL-backed repository without
     * changing the domain or use-cases.
     *
     * @return Measure[]
     */
    public function findAll(): array
    {
        $data = require __DIR__ . '/../Data/measures.php';

        $list = [];
        foreach ($data as $item) {
            $list[] = new Measure(
                $item['id'],
                $item['code'],
                $item['name'],
                $item['description'] ?? null
            );
        }

        return $list;
    }

    public function findById(string $id)
    {
        $data = require __DIR__ . '/../Data/measures.php';

        foreach ($data as $item) {
            if ($item['id'] === $id || ($item['code'] ?? null) === $id) {
                return new Measure(
                    $item['id'],
                    $item['code'],
                    $item['name'],
                    $item['description'] ?? null
                );
            }
        }

        return null;
    }

    public function save($measure): void
    {
        // No-op for file-backed array. In future this method will persist to DB.
    }
}