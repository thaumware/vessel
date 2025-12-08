<?php

namespace App\Stock\Infrastructure\In\Http\Controllers;

use App\Stock\Application\UseCases\ApplyMovement;
use App\Stock\Infrastructure\Out\Models\Eloquent\MovementModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class MovementWebhookController
{
    public function __construct(private ApplyMovement $applyMovement)
    {
    }

    public function receive(Request $request): JsonResponse
    {

        $payload = $request->validate([
            'sku' => 'required|string|max:255',
            'from_location_id' => 'nullable|uuid',
            'from_location_type' => 'nullable|string|max:50',
            'to_location_id' => 'nullable|uuid',
            'to_location_type' => 'nullable|string|max:50',
            'quantity' => 'required|integer|min:1',
            'movement_id' => 'nullable|string|max:255',
            'movement_type' => 'nullable|string|max:' . MovementModel::MAX_MOVEMENT_TYPE_LENGTH,
            'reference' => 'nullable|string|max:' . MovementModel::MAX_REFERENCE_LENGTH,
            'user_id' => 'nullable|uuid',
            'workspace_id' => 'nullable|uuid',
            'meta' => 'nullable|array',
        ]);

        $this->applyMovement->execute(
            $payload['sku'],
            $payload['from_location_id'] ?? null,
            $payload['to_location_id'] ?? null,
            (int) $payload['quantity'],
            $payload['from_location_type'] ?? null,
            $payload['to_location_type'] ?? null,
            $payload['movement_id'] ?? null,
            $payload['movement_type'] ?? null,
            $payload['reference'] ?? null,
            $payload['user_id'] ?? null,
            $payload['workspace_id'] ?? null,
            $payload['meta'] ?? null
        );

        return response()->json(['status' => 'ok']);
    }
}
