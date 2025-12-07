<?php

namespace App\Stock\Infrastructure\In\Http\Controllers;

use App\Stock\Application\UseCases\CreateBatch;
use App\Stock\Infrastructure\In\Http\Requests\CreateBatchRequest;
use Illuminate\Http\JsonResponse;

final class BatchController
{
    public function __construct(private CreateBatch $createBatch)
    {
    }

    public function create(CreateBatchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $batch = $this->createBatch->execute($data['id'], $data['sku'], $data['location_id'], $data['quantity'], $data['lot_number'] ?? null);

        return response()->json(['id' => $batch->id(), 'sku' => $batch->sku(), 'quantity' => $batch->quantity()], 201);
    }

    // stubs for future endpoints
    public function list(): JsonResponse
    {
        return response()->json([]);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(null);
    }
}
