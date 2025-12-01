<?php

namespace App\Uom\Domain\Services;

use App\Uom\Domain\Entities\Conversion;
use App\Uom\Domain\Interfaces\ConversionRepository;
use App\Uom\Domain\Interfaces\MeasureRepository;

/**
 * Servicio de dominio para conversión de unidades de medida
 */
class ConversionService
{
    public function __construct(
        private ConversionRepository $conversionRepository,
        private MeasureRepository $measureRepository,
    ) {}

    /**
     * Convierte un valor de una unidad a otra
     * 
     * @throws \InvalidArgumentException Si las medidas no existen
     * @throws \RuntimeException Si no se puede encontrar una ruta de conversión
     */
    public function convert(float $value, string $fromMeasureId, string $toMeasureId): float
    {
        // Si son la misma medida, devolver el mismo valor
        if ($fromMeasureId === $toMeasureId) {
            return $value;
        }

        // Buscar conversión directa
        $directConversion = $this->conversionRepository->findConversion($fromMeasureId, $toMeasureId);
        
        if ($directConversion !== null) {
            return $directConversion->convert($value);
        }

        // Buscar conversión inversa (y aplicar operación inversa)
        $inverseConversion = $this->conversionRepository->findConversion($toMeasureId, $fromMeasureId);
        
        if ($inverseConversion !== null) {
            return $this->applyInverseConversion($value, $inverseConversion);
        }

        // Intentar encontrar una ruta a través de la base
        $path = $this->findConversionPath($fromMeasureId, $toMeasureId);
        
        if ($path !== null) {
            return $this->applyConversionPath($value, $path);
        }

        throw new \RuntimeException(
            "No conversion path found from '{$fromMeasureId}' to '{$toMeasureId}'"
        );
    }

    /**
     * Verifica si existe una conversión entre dos medidas
     */
    public function canConvert(string $fromMeasureId, string $toMeasureId): bool
    {
        if ($fromMeasureId === $toMeasureId) {
            return true;
        }

        try {
            $this->convert(1.0, $fromMeasureId, $toMeasureId);
            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }

    /**
     * Obtiene el factor de conversión entre dos unidades
     */
    public function getConversionFactor(string $fromMeasureId, string $toMeasureId): ?float
    {
        if ($fromMeasureId === $toMeasureId) {
            return 1.0;
        }

        try {
            return $this->convert(1.0, $fromMeasureId, $toMeasureId);
        } catch (\RuntimeException) {
            return null;
        }
    }

    /**
     * Aplica una conversión inversa
     */
    private function applyInverseConversion(float $value, Conversion $conversion): float
    {
        return match ($conversion->getOperation()) {
            'mul' => $value / $conversion->getFactor(),
            'div' => $value * $conversion->getFactor(),
            'add' => $value - $conversion->getFactor(),
            'sub' => $value + $conversion->getFactor(),
            default => throw new \InvalidArgumentException("Invalid operation"),
        };
    }

    /**
     * Busca una ruta de conversión a través de medidas intermedias
     * Usa BFS para encontrar la ruta más corta
     * 
     * @return Conversion[]|null
     */
    private function findConversionPath(string $fromMeasureId, string $toMeasureId): ?array
    {
        // Obtener las medidas origen y destino
        $fromMeasure = $this->measureRepository->findById($fromMeasureId);
        $toMeasure = $this->measureRepository->findById($toMeasureId);

        if ($fromMeasure === null || $toMeasure === null) {
            return null;
        }

        // Solo se pueden convertir medidas de la misma categoría
        if ($fromMeasure->getCategory() !== $toMeasure->getCategory()) {
            return null;
        }

        // BFS para encontrar ruta
        $visited = [$fromMeasureId => true];
        $queue = [[$fromMeasureId, []]];

        while (!empty($queue)) {
            [$currentId, $path] = array_shift($queue);

            // Obtener todas las conversiones desde la medida actual
            $conversions = $this->conversionRepository->findConversionsFrom($currentId);
            $inverseConversions = $this->conversionRepository->findConversionsTo($currentId);

            // Combinar conversiones directas e inversas
            $allConversions = [];
            foreach ($conversions as $conv) {
                $allConversions[] = ['conversion' => $conv, 'inverse' => false];
            }
            foreach ($inverseConversions as $conv) {
                $allConversions[] = ['conversion' => $conv, 'inverse' => true];
            }

            foreach ($allConversions as $item) {
                $conv = $item['conversion'];
                $isInverse = $item['inverse'];
                
                $nextId = $isInverse ? $conv->getFromMeasureId() : $conv->getToMeasureId();

                if (isset($visited[$nextId])) {
                    continue;
                }

                $newPath = array_merge($path, [['conversion' => $conv, 'inverse' => $isInverse]]);

                if ($nextId === $toMeasureId) {
                    return $newPath;
                }

                $visited[$nextId] = true;
                $queue[] = [$nextId, $newPath];
            }
        }

        return null;
    }

    /**
     * Aplica una ruta de conversiones
     */
    private function applyConversionPath(float $value, array $path): float
    {
        $result = $value;

        foreach ($path as $step) {
            $conversion = $step['conversion'];
            $isInverse = $step['inverse'];

            if ($isInverse) {
                $result = $this->applyInverseConversion($result, $conversion);
            } else {
                $result = $conversion->convert($result);
            }
        }

        return $result;
    }
}
