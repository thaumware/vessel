<?php

namespace App\Uom\Domain\Entities;

class Conversion
{

    private string $from_measure_id;
    private string $to_measure_id;
    private float $factor;
    // add, sub, mul, div
    private string $operation;

    public function __construct(
        string $from_measure_id,
        string $to_measure_id,
        float $factor,
        string $operation)
    {
        $this->from_measure_id = $from_measure_id;
        $this->to_measure_id = $to_measure_id;
        $this->factor = $factor;
        $this->operation = $operation;
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
            'from_measure_id' => $this->from_measure_id,
            'to_measure_id' => $this->to_measure_id,
            'factor' => $this->factor,
            'operation' => $this->operation,
        ];
    }
}