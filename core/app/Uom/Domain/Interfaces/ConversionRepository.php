<?php

namespace App\Uom\Domain\Interfaces;

use App\Uom\Domain\Entities\Conversion;

interface ConversionRepository
{
    /**
     * Busca una conversión directa entre dos medidas
     */
    public function findConversion(string $fromMeasureId, string $toMeasureId): ?Conversion;

    /**
     * Obtiene todas las conversiones de una medida origen
     * 
     * @return Conversion[]
     */
    public function findConversionsFrom(string $measureId): array;

    /**
     * Obtiene todas las conversiones hacia una medida destino
     * 
     * @return Conversion[]
     */
    public function findConversionsTo(string $measureId): array;

    /**
     * Obtiene todas las conversiones
     * 
     * @return Conversion[]
     */
    public function findAll(): array;

    /**
     * Guarda una conversión
     */
    public function save(Conversion $conversion): void;

    /**
     * Elimina una conversión
     */
    public function delete(string $fromMeasureId, string $toMeasureId): void;
}
