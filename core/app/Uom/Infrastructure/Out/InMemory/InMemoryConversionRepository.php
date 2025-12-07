<?php

namespace App\Uom\Infrastructure\Out\InMemory;

use App\Uom\Domain\Entities\Conversion;
use App\Uom\Domain\Interfaces\ConversionRepository;

class InMemoryConversionRepository implements ConversionRepository
{
    /** @var array<string, Conversion> Key: "fromId:toId" */
    private array $conversions = [];

    /**
     * Pre-load with base conversions from data file.
     */
    public function __construct(bool $loadBaseData = false)
    {
        if ($loadBaseData) {
            $this->loadBaseData();
        }
    }

    public function loadBaseData(): void
    {
        $data = require __DIR__ . '/../Data/conversions.php';

        foreach ($data as $item) {
            $conversion = new Conversion(
                fromMeasureId: $item['from'],
                toMeasureId: $item['to'],
                factor: (float) $item['factor'],
                operation: $item['operation'] ?? 'mul',
            );
            $key = $this->makeKey($conversion->getFromMeasureId(), $conversion->getToMeasureId());
            $this->conversions[$key] = $conversion;
        }
    }

    public function findConversion(string $fromMeasureId, string $toMeasureId): ?Conversion
    {
        $key = $this->makeKey($fromMeasureId, $toMeasureId);
        return $this->conversions[$key] ?? null;
    }

    /**
     * @return Conversion[]
     */
    public function findConversionsFrom(string $measureId): array
    {
        return array_values(array_filter(
            $this->conversions,
            fn(Conversion $c) => $c->getFromMeasureId() === $measureId
        ));
    }

    /**
     * @return Conversion[]
     */
    public function findConversionsTo(string $measureId): array
    {
        return array_values(array_filter(
            $this->conversions,
            fn(Conversion $c) => $c->getToMeasureId() === $measureId
        ));
    }

    /**
     * @return Conversion[]
     */
    public function findAll(): array
    {
        return array_values($this->conversions);
    }

    public function save(Conversion $conversion): void
    {
        $key = $this->makeKey($conversion->getFromMeasureId(), $conversion->getToMeasureId());
        $this->conversions[$key] = $conversion;
    }

    public function delete(string $fromMeasureId, string $toMeasureId): void
    {
        $key = $this->makeKey($fromMeasureId, $toMeasureId);
        unset($this->conversions[$key]);
    }

    public function clear(): void
    {
        $this->conversions = [];
    }

    private function makeKey(string $fromId, string $toId): string
    {
        return "{$fromId}:{$toId}";
    }
}
