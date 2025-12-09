<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ValidateReservation;

use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;

/**
 * Valida si se puede reservar stock en una locación.
 * 
 * Combina información de:
 * - Stock actual (StockItemRepository)
 * - Configuración de locación (LocationStockSettingsRepository)
 * - Información del item (CatalogGateway)
 * - Información de locación (LocationGateway)
 * 
 * NO modifica estado, solo valida.
 */
class ValidateReservationUseCase
{
    public function __construct(
        private StockItemRepositoryInterface $stockRepository,
        private LocationStockSettingsRepositoryInterface $settingsRepository,
        private CatalogGatewayInterface $catalogGateway,
        private LocationGatewayInterface $locationGateway
    ) {
    }

    public function execute(ReservationValidationRequest $request): ReservationValidationResult
    {
        $warnings = [];

        // 1. Validar que el item existe en catálogo
        $itemInfo = $this->catalogGateway->getItem($request->itemId);
        if ($itemInfo === null) {
            return ReservationValidationResult::denied(
                reason: "Item '{$request->itemId}' no existe en catálogo"
            );
        }

        // 2. Validar que la locación existe
        $locationInfo = $this->locationGateway->getLocation($request->locationId);
        if ($locationInfo === null) {
            return ReservationValidationResult::denied(
                reason: "Locación '{$request->locationId}' no existe",
                itemInfo: $itemInfo
            );
        }

        // 3. Obtener stock actual
        $stockItem = $this->stockRepository->findByItemAndLocation(
            $request->itemId,
            $request->locationId
        );

        if ($stockItem === null) {
            return ReservationValidationResult::denied(
                reason: "No hay stock del item en esta locación",
                itemInfo: $itemInfo,
                locationInfo: $locationInfo
            );
        }

        // 4. Obtener configuración de locación
        $settings = $this->settingsRepository->findByLocationId($request->locationId);
        
        // 5. Validar cantidad disponible
        $available = $stockItem->getAvailableQuantity();
        
        if ($available < $request->quantity) {
            // Verificar si permite stock negativo
            if ($settings && $settings->allowsNegativeStock()) {
                $warnings[] = "La reserva dejará stock disponible negativo ({$available} - {$request->quantity} = " . ($available - $request->quantity) . ")";
            } else {
                return ReservationValidationResult::denied(
                    reason: "Stock disponible insuficiente. Disponible: {$available}, solicitado: {$request->quantity}",
                    availableQuantity: $available,
                    reservedQuantity: $stockItem->getReservedQuantity(),
                    totalQuantity: $stockItem->getQuantity(),
                    itemInfo: $itemInfo,
                    locationInfo: $locationInfo
                );
            }
        }

        // 6. Validar límite de reserva (porcentaje máximo)
        $maxReservationAllowed = null;
        if ($settings && $settings->getMaxReservationPercentage() !== null) {
            $maxPercentage = $settings->getMaxReservationPercentage();
            $maxReservationAllowed = ($stockItem->getQuantity() * $maxPercentage) / 100;
            
            $futureReserved = $stockItem->getReservedQuantity() + $request->quantity;
            
            if ($futureReserved > $maxReservationAllowed) {
                return ReservationValidationResult::denied(
                    reason: "Excede el límite de reserva ({$maxPercentage}% del stock total). Máximo permitido: {$maxReservationAllowed}, se alcanzaría: {$futureReserved}",
                    availableQuantity: $available,
                    reservedQuantity: $stockItem->getReservedQuantity(),
                    totalQuantity: $stockItem->getQuantity(),
                    itemInfo: $itemInfo,
                    locationInfo: $locationInfo
                );
            }
        }

        // 7. Validar lote si se especificó
        if ($request->lotId !== null) {
            // TODO: Validar que el lote existe y no está vencido
            $warnings[] = "Validación de lote no implementada completamente";
        }

        // ✅ Validación exitosa
        return ReservationValidationResult::allowed(
            availableQuantity: $available,
            reservedQuantity: $stockItem->getReservedQuantity(),
            totalQuantity: $stockItem->getQuantity(),
            maxReservationAllowed: $maxReservationAllowed,
            warnings: $warnings,
            itemInfo: $itemInfo,
            locationInfo: $locationInfo
        );
    }
}
