<?php

namespace App\Uom\Domain\Entities;

class Conversion
{
    private string $fromMeasureId;
    private string $toMeasureId;
    private float $factor;
    // add, sub, mul, div
    private string $operation;

    public function __construct(
        string $fromMeasureId,
        string $toMeasureId,
        float $factor,
        string $operation
    ) {
        $this->fromMeasureId = $fromMeasureId;
        $this->toMeasureId = $toMeasureId;
        $this->factor = $factor;
        $this->operation = $operation;
    }

    public function getFromMeasureId(): string
    {
        return $this->fromMeasureId;
    }

    public function getToMeasureId(): string
    {
        return $this->toMeasureId;
    }

    public function getFactor(): float
    {
        return $this->factor;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function convert(float $value): float
    {
        return match ($this->operation) {
            'add' => $value + $this->factor,
            'sub' => $value - $this->factor,
            'mul' => $value * $this->factor,
            'div' => $value / $this->factor,
            default => throw new \InvalidArgumentException("Invalid operation: {$this->operation}"),
        };
    }

    public function toArray(): array
    {
        return [
            'from_measure_id' => $this->fromMeasureId,
            'to_measure_id' => $this->toMeasureId,
            'factor' => $this->factor,
            'operation' => $this->operation,
        ];
    }
}