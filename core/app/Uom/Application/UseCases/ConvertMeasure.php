<?php

namespace App\Uom\Application\UseCases;

use App\Uom\Domain\Services\ConversionService;

/**
 * Caso de uso para convertir valores entre unidades de medida
 */
class ConvertMeasure
{
    public function __construct(
        private ConversionService $conversionService,
    ) {}

    /**
     * Convierte un valor de una unidad a otra
     * 
     * @param float $value El valor a convertir
     * @param string $fromMeasureId ID de la medida origen
     * @param string $toMeasureId ID de la medida destino
     * @return array Resultado de la conversión con metadatos
     */
    public function execute(float $value, string $fromMeasureId, string $toMeasureId): array
    {
        $convertedValue = $this->conversionService->convert($value, $fromMeasureId, $toMeasureId);
        $factor = $this->conversionService->getConversionFactor($fromMeasureId, $toMeasureId);

        return [
            'original_value' => $value,
            'original_measure_id' => $fromMeasureId,
            'converted_value' => $convertedValue,
            'target_measure_id' => $toMeasureId,
            'conversion_factor' => $factor,
        ];
    }

    /**
     * Verifica si es posible realizar una conversión
     */
    public function canConvert(string $fromMeasureId, string $toMeasureId): bool
    {
        return $this->conversionService->canConvert($fromMeasureId, $toMeasureId);
    }

    /**
     * Obtiene solo el valor convertido (método simplificado)
     */
    public function convertValue(float $value, string $fromMeasureId, string $toMeasureId): float
    {
        return $this->conversionService->convert($value, $fromMeasureId, $toMeasureId);
    }
}
