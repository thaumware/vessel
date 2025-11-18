<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Entities\Batch;
use App\Stock\Domain\Interfaces\BatchRepositoryInterface;

final class CreateBatch
{
    public function __construct(private BatchRepositoryInterface $batches)
    {
    }

    public function execute(string $id, string $sku, string $locationId, int $quantity, ?string $lotNumber = null): Batch
    {
        $batch = new Batch($id, $sku, $locationId, $quantity, $lotNumber);
        return $this->batches->save($batch);
    }
}
