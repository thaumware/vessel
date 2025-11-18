<?php

namespace App\Stock\Infrastructure\In\Http\Controllers;

use App\Stock\Application\UseCases\CreateUnit;
use App\Stock\Infrastructure\In\Http\Requests\CreateUnitRequest;
use Illuminate\Http\JsonResponse;

final class UnitController
{
    public function __construct(private CreateUnit $createUnit)
    {
    }

    public function create(CreateUnitRequest $request): JsonResponse
    {
        $data = $request->validated();
        $unit = $this->createUnit->execute($data['id'], $data['code'], $data['name']);

        return response()->json(['id' => $unit->id(), 'code' => $unit->code(), 'name' => $unit->name()], 201);
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
