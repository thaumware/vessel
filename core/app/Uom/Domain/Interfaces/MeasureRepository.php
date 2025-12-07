<?php

namespace App\Uom\Domain\Interfaces;

use App\Uom\Domain\Entities\Measure;

interface MeasureRepository
{
    public function findById(string $id): ?Measure;

    /**
     * @return Measure[]
     */
    public function findAll(): array;

    /**
     * @return Measure[]
     */
    public function findByCategory(string $category): array;

    /**
     * @return Measure[]
     */
    public function findBaseMeasures(): array;

    public function save($measure): void;

    public function update($measure): void;

    public function delete(string $id): void;
}