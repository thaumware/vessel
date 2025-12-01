<?php

declare(strict_types=1);

namespace App\Stock\Domain\Services;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Entities\Lot;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\LotRepositoryInterface;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\ValidationResult;

/**
 * Servicio de dominio para procesar movimientos de stock.
 * 
 * Coordina la lógica de negocio de movimientos:
 * - Valida pre-condiciones
 * - Ejecuta el movimiento
 * - Actualiza stock items y lotes
 * - Registra balance after
 */
class StockMovementService
{
    public function __construct(
        private MovementRepositoryInterface $movementRepository,
        private StockItemRepositoryInterface $stockItemRepository,
        private ?LotRepositoryInterface $lotRepository = null,
        private ?StockCapacityService $capacityService = null
    ) {
    }

    /**
     * Procesa un movimiento y actualiza el stock.
     */
    public function process(Movement $movement): ProcessMovementResult
    {
        // Validar pre-condiciones
        $validation = $this->validate($movement);
        if ($validation->isInvalid()) {
            return ProcessMovementResult::failure($movement, $validation->getErrors());
        }

        // Obtener o crear stock item
        $stockItem = $this->getOrCreateStockItem($movement);
        $previousBalance = $stockItem->getQuantity();

        // Procesar según tipo de movimiento
        $stockItem = $this->applyMovement($stockItem, $movement);

        // Actualizar balance en movimiento
        $movement = $movement->withBalanceAfter($stockItem->getQuantity());

        // Marcar como completado
        $movement = $movement->markAsCompleted();

        // Registrar lote si aplica
        $this->ensureLotExists($movement);

        // Persistir
        $this->stockItemRepository->save($stockItem);
        $this->movementRepository->save($movement);

        return ProcessMovementResult::success($movement, $stockItem, $previousBalance);
    }

    /**
     * Valida un movimiento antes de procesarlo.
     */
    public function validate(Movement $movement): ValidationResult
    {
        $result = ValidationResult::valid();

        // Verificar que puede procesarse
        if (!$movement->canProcess()) {
            $result = $result->addError(
                "El movimiento en estado {$movement->getStatus()->value} no puede procesarse"
            );
        }

        // Verificar stock suficiente para salidas
        if ($movement->isOutbound()) {
            $result = $result->merge($this->validateOutbound($movement));
        }

        // Verificar producto no vencido
        if ($movement->isExpired()) {
            $result = $result->addError("El lote está vencido y no puede procesarse");
        }

        // Validar capacidad para ingresos
        if ($movement->isInbound()) {
            $result = $result->merge($this->validateCapacity($movement));
        }

        // Validar reservas y liberaciones
        if ($movement->getType() === MovementType::RESERVE) {
            $result = $result->merge($this->validateReservation($movement));
        }

        if ($movement->getType() === MovementType::RELEASE) {
            $result = $result->merge($this->validateRelease($movement));
        }

        return $result;
    }

    // === Validaciones privadas ===

    private function validateOutbound(Movement $movement): ValidationResult
    {
        $stockItem = $this->findStockItem($movement);

        if ($stockItem === null) {
            return ValidationResult::withError(
                "No hay stock de {$movement->getSku()} en la ubicación"
            );
        }

        if ($stockItem->getAvailableQuantity() < $movement->getQuantity()) {
            return ValidationResult::withError(
                "Stock insuficiente. Disponible: {$stockItem->getAvailableQuantity()}, requerido: {$movement->getQuantity()}"
            );
        }

        return ValidationResult::valid();
    }

    private function validateCapacity(Movement $movement): ValidationResult
    {
        if ($this->capacityService === null) {
            return ValidationResult::valid();
        }

        $capacityResult = $this->capacityService->canAcceptStock(
            locationId: $movement->getLocationId(),
            quantity: $movement->getQuantity(),
            sku: $movement->getSku()
        );

        if ($capacityResult->isInvalid()) {
            return ValidationResult::withError(
                $capacityResult->getErrorMessage() ?? 'Error de capacidad'
            );
        }

        return ValidationResult::valid();
    }

    private function validateReservation(Movement $movement): ValidationResult
    {
        $stockItem = $this->findStockItem($movement);

        if ($stockItem === null) {
            return ValidationResult::withError("No hay stock para reservar");
        }

        if ($stockItem->getAvailableQuantity() < $movement->getQuantity()) {
            return ValidationResult::withError(
                "Stock disponible insuficiente para reservar. Disponible: {$stockItem->getAvailableQuantity()}, requerido: {$movement->getQuantity()}"
            );
        }

        return ValidationResult::valid();
    }

    private function validateRelease(Movement $movement): ValidationResult
    {
        $stockItem = $this->findStockItem($movement);

        if ($stockItem === null) {
            return ValidationResult::withError("No hay stock para liberar");
        }

        if ($stockItem->getReservedQuantity() < $movement->getQuantity()) {
            return ValidationResult::withError(
                "No hay suficiente cantidad reservada para liberar. Reservado: {$stockItem->getReservedQuantity()}, intentando liberar: {$movement->getQuantity()}"
            );
        }

        return ValidationResult::valid();
    }

    // === Helpers ===

    private function findStockItem(Movement $movement): ?StockItem
    {
        return $this->stockItemRepository->findBySkuAndLocation(
            $movement->getSku(),
            $movement->getLocationId()
        );
    }

    private function applyMovement(StockItem $stockItem, Movement $movement): StockItem
    {
        $type = $movement->getType();
        $quantity = $movement->getQuantity();

        return match (true) {
            $type->addsStock() => $stockItem->adjustQuantity($quantity),
            $type->removesStock() => $stockItem->adjustQuantity(-$quantity),
            $type === MovementType::RESERVE => $stockItem->reserve($quantity),
            $type === MovementType::RELEASE => $stockItem->release($quantity),
            default => $stockItem, // Tipos neutros no modifican
        };
    }

    private function getOrCreateStockItem(Movement $movement): StockItem
    {
        $stockItem = $this->findStockItem($movement);

        if ($stockItem !== null) {
            return $stockItem;
        }

        // Solo crear para movimientos de entrada
        if (!$movement->isInbound()) {
            throw new \DomainException(
                "No existe stock de {$movement->getSku()} en la ubicación"
            );
        }

        return new StockItem(
            id: $this->generateId(),
            sku: $movement->getSku(),
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $movement->getLocationId(),
            locationType: 'default',
            quantity: 0,
            reservedQuantity: 0,
            lotNumber: $movement->getLotNumber(),
            expirationDate: $movement->getExpirationDate(),
            serialNumber: null,
            workspaceId: $movement->getWorkspaceId()
        );
    }

    private function ensureLotExists(Movement $movement): void
    {
        if ($this->lotRepository === null || !$movement->hasLot() || !$movement->isInbound()) {
            return;
        }

        $lot = $this->lotRepository->findByLotNumber($movement->getLotNumber());

        if ($lot === null) {
            $lot = Lot::create(
                id: $this->generateId(),
                lotNumber: $movement->getLotNumber(),
                sku: $movement->getSku(),
                expirationDate: $movement->getExpirationDate(),
                workspaceId: $movement->getWorkspaceId()
            );
            $this->lotRepository->save($lot);
        }
    }

    private function generateId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
