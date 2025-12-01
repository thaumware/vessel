<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\GetLocationSettings;

use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use App\Stock\Domain\Services\StockCapacityService;

/**
 * Use Case para obtener configuracion y estadisticas de capacidad.
 */
class GetLocationSettingsUseCase
{
    public function __construct(
        private LocationStockSettingsRepositoryInterface $settingsRepository,
        private StockCapacityService $capacityService,
    ) {}

    /**
     * Obtiene la configuracion de una ubicacion.
     */
    public function getSettings(string $locationId): ?array
    {
        $settings = $this->settingsRepository->findByLocationId($locationId);

        return $settings?->toArray();
    }

    /**
     * Obtiene estadisticas de capacidad completas.
     */
    public function getCapacityStats(string $locationId): array
    {
        return $this->capacityService->getCapacityStats($locationId);
    }

    /**
     * Verifica si una ubicacion puede aceptar stock.
     */
    public function canAcceptStock(
        string $locationId,
        int $quantity,
        ?string $sku = null,
        ?string $itemType = null
    ): array {
        $result = $this->capacityService->canAcceptStock($locationId, $quantity, $sku, $itemType);

        return $result->toArray();
    }

    /**
     * Obtiene la capacidad disponible.
     */
    public function getAvailableCapacity(string $locationId): ?int
    {
        return $this->capacityService->getAvailableCapacity($locationId);
    }

    /**
     * Verifica si la ubicacion esta llena.
     */
    public function isLocationFull(string $locationId): bool
    {
        return $this->capacityService->isLocationFull($locationId);
    }
}
