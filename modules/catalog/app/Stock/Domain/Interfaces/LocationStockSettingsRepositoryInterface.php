<?php

declare(strict_types=1);

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\LocationStockSettings;

/**
 * Interface para persistencia de configuraciones de stock por ubicacion.
 */
interface LocationStockSettingsRepositoryInterface
{
    /**
     * Buscar configuracion por ID
     */
    public function findById(string $id): ?LocationStockSettings;

    /**
     * Buscar configuracion por location_id
     */
    public function findByLocationId(string $locationId): ?LocationStockSettings;

    /**
     * Buscar configuraciones para multiples ubicaciones
     * 
     * @param array<string> $locationIds
     * @return array<string, LocationStockSettings> Indexed by location_id
     */
    public function findByLocationIds(array $locationIds): array;

    /**
     * Obtener todas las configuraciones activas
     * 
     * @return array<LocationStockSettings>
     */
    public function findAllActive(): array;

    /**
     * Guardar configuracion (create o update)
     */
    public function save(LocationStockSettings $settings): LocationStockSettings;

    /**
     * Eliminar configuracion
     */
    public function delete(string $id): bool;

    /**
     * Verificar si existe configuracion para una ubicacion
     */
    public function existsForLocation(string $locationId): bool;
}
