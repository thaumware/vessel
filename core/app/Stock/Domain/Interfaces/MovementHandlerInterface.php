<?php

declare(strict_types=1);

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;

/**
 * Handler para tipos de movimiento custom/extendidos.
 * 
 * Permite agregar nuevos tipos de movimiento sin modificar el enum MovementType.
 * 
 * Ejemplo de uso:
 * 
 * ```php
 * class CustomerLoanHandler implements MovementHandlerInterface
 * {
 *     public function supports(string $movementType): bool
 *     {
 *         return $movementType === 'customer_loan';
 *     }
 * 
 *     public function handle(Movement $movement, StockItem $stockItem): StockItem
 *     {
 *         // IMPORTANTE: StockItem es INMUTABLE, debes retornar el nuevo item
 *         return $stockItem->adjustQuantity(-$movement->getQuantity());
 *     }
 * 
 *     public function validate(Movement $movement, StockItem $stockItem): void
 *     {
 *         if ($stockItem->getQuantity() < $movement->getQuantity()) {
 *             throw new \DomainException('Stock insuficiente');
 *         }
 *     }
 * }
 * ```
 */
interface MovementHandlerInterface
{
    /**
     * ¿Este handler soporta este tipo de movimiento?
     * 
     * @param string $movementType El tipo personalizado (ej: 'customer_loan', 'repair_in', etc.)
     */
    public function supports(string $movementType): bool;

    /**
     * Valida el movimiento ANTES de aplicarlo.
     * 
     * @throws \DomainException Si la validación falla
     */
    public function validate(Movement $movement, StockItem $stockItem): void;

    /**
     * Aplica el movimiento al stock.
     * 
     * IMPORTANTE: StockItem es INMUTABLE. Debes retornar el nuevo StockItem modificado.
     * 
     * @return StockItem El StockItem modificado (nuevo objeto)
     */
    public function handle(Movement $movement, StockItem $stockItem): StockItem;

    /**
     * Describe el comportamiento del movimiento (para auditoría/logs).
     */
    public function describe(): string;
}
