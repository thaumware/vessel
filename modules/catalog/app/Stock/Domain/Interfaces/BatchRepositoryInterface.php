<?php

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\Batch;

interface BatchRepositoryInterface
{
    public function save(Batch $batch): Batch;

    public function findById(string $id): ?Batch;

    public function findBySkuAndLocation(string $sku, string $locationId): ?Batch;
}
