<?php

declare(strict_types=1);

namespace App\Stock\Domain\Services;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Entities\Lot;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\LotRepositoryInterface;
use App\Stock\Domain\Interfaces\MovementHandlerInterface;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\ValidationResult;
use Illuminate\Support\Str;

/**
 * Servicio de dominio para procesar movimientos de stock.
 * 
 * Coordina la lógica de negocio de movimientos:
 * - Valida pre-condiciones (configurable)
 * - Ejecuta el movimiento (estándar o custom via handlers)
 * - Actualiza stock items y lotes
 * 
 * EXTENSIBILIDAD:
 * Puede recibir un array de MovementHandlerInterface para soportar tipos custom.
 */
class StockMovementService
{
    /** @var MovementHandlerInterface[] */
    private array $customHandlers = [];

    public function __construct(
        private MovementRepositoryInterface $movementRepository,
        private StockItemRepositoryInterface $stockItemRepository,
        private ?LotRepositoryInterface $lotRepository = null,
        private ?StockCapacityService $capacityService = null,
        private bool $allowNegativeStock = false,
        array $customHandlers = []
    ) {
        $this->customHandlers = $customHandlers;
    }

    /**
     * Procesa un movimiento y actualiza el stock.
     * 
     * Soporta handlers custom para tipos extendidos.
     */
    public function process(Movement $movement): ProcessMovementResult
    {
        // Obtener o crear stock item
        $stockItem = $this->getOrCreateStockItem($movement);
        $previousBalance = $stockItem->getQuantity();

        // Validar pre-condiciones (estándar O custom)
        $validation = $this->validateMovement($movement, $stockItem);
        if ($validation->isInvalid()) {
            return ProcessMovementResult::failure($movement, $validation->getErrors());
        }

        // Procesar según tipo: custom handler O lógica estándar
        $stockItem = $this->applyMovement($stockItem, $movement);

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
     * Ruta PRIVADA - usa validateMovement() para incluir custom handlers.
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

        // Verificar lote no vencido (si tiene lote asociado)
        if ($movement->hasLot() && $this->lotRepository !== null) {
            $result = $result->merge($this->validateLotExpiration($movement));
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

    /**
     * Validación completa: estándar + custom handlers.
     */
    private function validateMovement(Movement $movement, StockItem $stockItem): ValidationResult
    {
        // 1. Intentar handler custom primero
        $handler = $this->findHandler($movement);
        if ($handler !== null) {
            try {
                $handler->validate($movement, $stockItem);
                return ValidationResult::valid();
            } catch (\DomainException $e) {
                return ValidationResult::withError($e->getMessage());
            }
        }

        // 2. Validación estándar (enum)
        return $this->validate($movement);
    }

    /**
     * Encuentra handler custom para un tipo de movimiento.
     */
    private function findHandler(Movement $movement): ?MovementHandlerInterface
    {
        // Solo buscar handler si el tipo es CUSTOM
        if ($movement->getType() !== MovementType::CUSTOM) {
            return null;
        }

        // El referenceType identifica el tipo custom
        $customType = $movement->getReferenceType();
        if ($customType === null) {
            return null;
        }

        foreach ($this->customHandlers as $handler) {
            if ($handler->supports($customType)) {
                return $handler;
            }
        }

        return null;
    }

    // === Validaciones privadas ===

    private function validateOutbound(Movement $movement): ValidationResult
    {
        // Si permite stock negativo, no validar
        if ($this->allowNegativeStock) {
            return ValidationResult::valid();
        }

        $stockItem = $this->findStockItem($movement);

        if ($stockItem === null) {
            return ValidationResult::withError(
                "No hay stock de {$movement->getItemId()} en la ubicación"
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
            itemId: $movement->getItemId()
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
        // Si permite stock negativo, no validar
        if ($this->allowNegativeStock) {
            return ValidationResult::valid();
        }

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
        // Si permite stock negativo, no validar
        if ($this->allowNegativeStock) {
            return ValidationResult::valid();
        }

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

    private function validateLotExpiration(Movement $movement): ValidationResult
    {
        if ($this->lotRepository === null || !$movement->hasLot()) {
            return ValidationResult::valid();
        }

        $lot = $this->lotRepository->findByLotNumber($movement->getLotId());
        
        if ($lot === null) {
            // Si el lote no existe aún, no hay validación de expiración
            return ValidationResult::valid();
        }

        if ($lot->isExpired()) {
            return ValidationResult::withError("El lote está vencido y no puede procesarse");
        }

        return ValidationResult::valid();
    }

    // === Helpers ===

    private function findStockItem(Movement $movement): ?StockItem
    {
        return $this->stockItemRepository->findByItemAndLocation(
            $movement->getItemId(),
            $movement->getLocationId()
        );
    }

    private function applyMovement(StockItem $stockItem, Movement $movement): StockItem
    {
        // 1. Intentar handler custom primero
        $handler = $this->findHandler($movement);
        if ($handler !== null) {
            return $handler->handle($movement, $stockItem);
        }

        // 2. Lógica estándar (enum)
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
                "No existe stock de {$movement->getItemId()} en la ubicación"
            );
        }

        return new StockItem(
            id: $this->generateId(),
            itemId: $movement->getItemId(),
            catalogItemId: null,
            catalogOrigin: null,
            locationId: $movement->getLocationId(),
            locationType: 'default',
            quantity: 0,
            reservedQuantity: 0,
            lotNumber: $movement->getLotId(),
            expirationDate: null,
            serialNumber: null,
            workspaceId: $movement->getWorkspaceId()
        );
    }

    private function ensureLotExists(Movement $movement): void
    {
        if ($this->lotRepository === null || !$movement->hasLot() || !$movement->isInbound()) {
            return;
        }

        $lot = $this->lotRepository->findByLotNumber($movement->getLotId());

        if ($lot === null) {
            $lot = new Lot(
                id: $this->generateId(),
                itemId: $movement->getItemId(),
                identifiers: ['lot_number' => $movement->getLotId()],
                workspaceId: $movement->getWorkspaceId()
            );
            $this->lotRepository->save($lot);
        }
    }

    private function generateId(): string
    {
        return (string) Str::uuid();
    }
}
