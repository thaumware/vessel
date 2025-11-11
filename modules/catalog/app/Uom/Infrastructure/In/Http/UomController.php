<?php

namespace App\Uom\Infrastructure\In\Http;


use App\Uom\Domain\Interfaces\MeasureRepository as MeasureRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Uom\Domain\Entities\Conversion;

class UomController
{
    public function __construct(private MeasureRepositoryInterface $repository)
    {
    }

    public function createMeasure(): JsonResponse
    {
        return response()->json(['message' => 'Measure created'], 201);
    }

    public function measureList(Request $request): JsonResponse
    {
        $measures = $this->repository->findAll();

        $data = array_map(fn($m) => $m->toArray(), $measures);

        return response()->json(['data' => $data], 200);
    }

    public function measureProfile(string $id): JsonResponse
    {
        $measure = $this->repository->findById($id);

        if (!$measure) {
            return response()->json(['message' => 'Measure not found'], 404);
        }

        return response()->json(['data' => $measure->toArray()], 200);
    }

    public function convertUom(Request $request): JsonResponse
    {
        $payload = $request->only(['from', 'to', 'value']);

        $from = $payload['from'] ?? null;
        $to = $payload['to'] ?? null;
        $value = isset($payload['value']) ? (float) $payload['value'] : null;

        if (!$from || !$to || $value === null) {
            return response()->json(['message' => 'Missing parameters, expected from,to,value'], 400);
        }

        $fromMeasure = $this->repository->findById($from);
        $toMeasure = $this->repository->findById($to);

        if (!$fromMeasure || !$toMeasure) {
            return response()->json(['message' => 'Measure not found'], 404);
        }

        $conversions = require __DIR__ . '/../../Out/Data/conversions.php';

        $found = null;
        foreach ($conversions as $c) {
            if (($c['from_measure_id'] === $from || $c['from_measure_id'] === $fromMeasure->getCode())
                && ($c['to_measure_id'] === $to || $c['to_measure_id'] === $toMeasure->getCode())) {
                $found = $c;
                break;
            }
        }

        if (!$found) {
            return response()->json(['message' => 'Conversion not defined'], 404);
        }

        $conversion = new Conversion(
            $fromMeasure->getId(),
            $toMeasure->getId(),
            (float) $found['factor'],
            $found['operation'] ?? 'mul'
        );

        try {
            $result = $conversion->convert($value);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json([
            'from' => $fromMeasure->toArray(),
            'to' => $toMeasure->toArray(),
            'value' => $value,
            'result' => $result,
        ], 200);
    }
}