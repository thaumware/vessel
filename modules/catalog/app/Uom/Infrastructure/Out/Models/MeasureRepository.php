<?php

namespace App\Uom\Infrastructure\Out\Models;

use App\Uom\Domain\Interfaces\MeasureRepository as MeasureRepositoryInterface;
use App\Uom\Domain\Entities\Measure;

class MeasureRepository implements MeasureRepositoryInterface
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
            $id = $item['id'] ?? ($item['code'] ?? null);
            $code = $item['code'] ?? $id;
            $list[] = new Measure(
                $id,
                $code,
                $item['name'] ?? $code,
                $item['description'] ?? null
            );
        }

        return $list;
    }

    public function findById(string $id)
    {
        $data = require __DIR__ . '/../Data/measures.php';

        foreach ($data as $item) {
            $itemId = $item['id'] ?? ($item['code'] ?? null);
            $itemCode = $item['code'] ?? $itemId;
            if ($itemId === $id || $itemCode === $id) {
                return new Measure(
                    $itemId,
                    $itemCode,
                    $item['name'] ?? $itemCode,
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