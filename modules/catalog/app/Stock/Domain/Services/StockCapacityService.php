<?php

declare(strict_types=1);

namespace App\Stock\Domain\Services;

use App\Stock\Domain\Entities\LocationStockSettings;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use App\Stock\Domain\Interfaces\StockRepositoryInterface;
use App\Stock\Domain\ValueObjects\CapacityValidationResult;

/**
 * Servicio de dominio para validar capacidad de stock en ubicaciones.
 * Considera la jerarquia de ubicaciones (padre + hijos).
 */
class StockCapacityService
{
    private LocationStockSettingsRepositoryInterface $settingsRepository;
    private LocationGatewayInterface $locationGateway;
    private StockRepositoryInterface $stockRepository;

    public function __construct(
        LocationStockSettingsRepositoryInterface $settingsRepository,
        LocationGatewayInterface $locationGateway,
        StockRepositoryInterface $stockRepository
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->locationGateway = $locationGateway;
        $this->stockRepository = $stockRepository;
    }

    /**
     * Valida si una ubicacion puede aceptar stock adicional.
     *
     * @param string $locationId ID de la ubicacion
     * @param float $quantity Cantidad a agregar
     * @param string|null $itemId ID del item (para validar mezcla de items)
     * @param string|null $itemType Tipo de item (para validar tipos permitidos)
     */
    public function canAcceptStock(
        string $locationId,
        float $quantity,
        ?string $itemId = null,
        ?string $itemType = null
    ): CapacityValidationResult {
        $settings = $this->settingsRepository->findByLocationId($locationId);

        // Sin configuracion = sin restricciones
        if ($settings === null) {
            return CapacityValidationResult::valid();
        }

        if (!$settings->isActive()) {
            return CapacityValidationResult::locationNotActive($locationId);
        }

        // Validar tipo de item permitido
        if ($itemType !== null && !$settings->isItemTypeAllowed($itemType)) {
            return CapacityValidationResult::itemTypeNotAllowed(
                $itemType,
                $settings->getAllowedItemTypes() ?? [],
                $locationId
            );
        }

        // Validar cantidad maxima
        if ($settings->getMaxQuantity() !== null) {
            $result = $this->validateMaxQuantity($locationId, $quantity, $settings);
            if ($result->isInvalid()) {
                return $result;
            }
        }

        // Validar mezcla de items
        if (!$settings->allowsMixedSkus() && $itemId !== null) {
            $result = $this->validateMixedItems($locationId, $itemId);
            if ($result->isInvalid()) {
                return $result;
            }
        }

        return CapacityValidationResult::valid();
    }

    /**
     * Obtiene el total de stock para una ubicacion y todos sus descendientes.
     */
    public function getTotalStockForLocationTree(string $locationId): float
    {
        $locationIds = $this->getLocationTreeIds($locationId);
        $total = 0.0;

        foreach ($locationIds as $id) {
            $stocks = $this->stockRepository->getByLocation($id);
            foreach ($stocks as $stock) {
                $total += $stock->quantity();
            }
        }

        return $total;
    }

    /**
     * Obtiene los IDs de items unicos en una ubicacion.
     *
     * @return string[]
     */
    public function getUniqueItemIds(string $locationId): array
    {
        $stocks = $this->stockRepository->getByLocation($locationId);
        $itemIds = [];

        foreach ($stocks as $stock) {
            $itemId = $stock->sku(); // TODO: Cambiar a itemId cuando Stock entity se actualice
            if (!in_array($itemId, $itemIds, true)) {
                $itemIds[] = $itemId;
            }
        }

        return $itemIds;
    }

    /**
     * Obtiene el espacio disponible en una ubicacion.
     *
     * @return float|null null si no hay limite configurado
     */
    public function getAvailableCapacity(string $locationId): ?float
    {
        $settings = $this->settingsRepository->findByLocationId($locationId);

        if ($settings === null || $settings->getMaxQuantity() === null) {
            return null; // Sin limite
        }

        $currentStock = $this->getTotalStockForLocationTree($locationId);
        $available = $settings->getMaxQuantity() - $currentStock;

        return max(0, $available);
    }

    /**
     * Verifica si una ubicacion esta llena.
     */
    public function isLocationFull(string $locationId): bool
    {
        $available = $this->getAvailableCapacity($locationId);

        return $available !== null && $available <= 0;
    }

    /**
     * Obtiene estadisticas de capacidad para una ubicacion.
     *
     * @return array<string, mixed>
     */
    public function getCapacityStats(string $locationId): array
    {
        $settings = $this->settingsRepository->findByLocationId($locationId);
        $currentStock = $this->getTotalStockForLocationTree($locationId);

        $maxQuantity = $settings?->getMaxQuantity();
        $usagePercent = null;
        $availableQuantity = null;

        if ($maxQuantity !== null && $maxQuantity > 0) {
            $availableQuantity = max(0, $maxQuantity - $currentStock);
            $usagePercent = round(($currentStock / $maxQuantity) * 100, 2);
        }

        return [
            'location_id' => $locationId,
            'current_quantity' => $currentStock,
            'max_quantity' => $maxQuantity,
            'available_quantity' => $availableQuantity,
            'usage_percent' => $usagePercent,
            'unique_items' => count($this->getUniqueItemIds($locationId)),
            'allow_mixed_items' => $settings?->allowsMixedSkus() ?? true, // TODO: rename setting
            'fifo_enforced' => $settings?->isFifoEnforced() ?? false,
            'is_active' => $settings?->isActive() ?? true,
        ];
    }

    /**
     * Obtiene la configuracion de una ubicacion.
     */
    public function getSettings(string $locationId): ?LocationStockSettings
    {
        return $this->settingsRepository->findByLocationId($locationId);
    }

    private function validateMaxQuantity(
        string $locationId,
        float $requestedQuantity,
        LocationStockSettings $settings
    ): CapacityValidationResult {
        $currentStock = $this->getTotalStockForLocationTree($locationId);
        $maxQuantity = $settings->getMaxQuantity();

        if ($maxQuantity !== null && ($currentStock + $requestedQuantity) > $maxQuantity) {
            return CapacityValidationResult::exceedsMaxQuantity(
                $currentStock,
                $requestedQuantity,
                $maxQuantity,
                $locationId
            );
        }

        return CapacityValidationResult::valid();
    }

    private function validateMixedItems(string $locationId, string $newItemId): CapacityValidationResult
    {
        $existingItemIds = $this->getUniqueItemIds($locationId);

        // Si no hay items existentes o el nuevo item ya existe, OK
        if (empty($existingItemIds) || in_array($newItemId, $existingItemIds, true)) {
            return CapacityValidationResult::valid();
        }

        return CapacityValidationResult::mixedItemsNotAllowed($locationId);
    }

    /**
     * Obtiene todos los IDs de la ubicacion y sus descendientes.
     *
     * @return string[]
     */
    private function getLocationTreeIds(string $locationId): array
    {
        $descendants = $this->locationGateway->getDescendantIds($locationId);

        return array_merge([$locationId], $descendants);
    }
}
