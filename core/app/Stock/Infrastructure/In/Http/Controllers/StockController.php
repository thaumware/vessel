<?php

namespace App\Stock\Infrastructure\In\Http\Controllers;

use App\Stock\Application\UseCases\CreateUnit;
use App\Stock\Application\UseCases\CreateBatch;
use App\Stock\Application\UseCases\GetStockByLocation;
use App\Stock\Infrastructure\In\Http\Requests\CreateUnitRequest;
use App\Stock\Infrastructure\In\Http\Requests\CreateBatchRequest;
use Illuminate\Http\JsonResponse;

final class StockController
{
    public function __construct(private GetStockByLocation $getStock)
    {
    }

    public function index(string $locationId): JsonResponse
    {
        $stocks = $this->getStock->execute($locationId);

        $payload = array_map(fn($s) => [
            'sku' => $s->sku(),
            'location_id' => $s->locationId(),
            'quantity' => $s->quantity(),
        ], $stocks);

        return response()->json($payload);
    }
}
