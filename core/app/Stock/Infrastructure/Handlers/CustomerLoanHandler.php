<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Handlers;

use App\Stock\Domain\Interfaces\MovementHandlerInterface;
use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;

/**
 * Handler EJEMPLO para movimientos de préstamo a cliente.
 * 
 * Tipo: 'customer_loan' (resta stock, pero permite devolución con 'loan_return')
 * 
 * USO:
 * ```php
 * POST /api/v1/stock/movements
 * {
 *   "type": "custom",                   // ✅ MovementType::CUSTOM
 *   "item_id": "ITEM-001",
 *   "location_id": "WAREHOUSE-MAIN",
 *   "quantity": 2,
 *   "reference_type": "customer_loan",  // ✅ Identifica el tipo custom
 *   "reference_id": "LOAN-2024-001",
 *   "meta": {
 *     "customer_id": "CUST-123",
 *     "expected_return_date": "2024-12-31",
 *     "loan_type": "demo"
 *   }
 * }
 * ```
 * 
 * REGISTRO (en ServiceProvider):
 * ```php
 * $registry = $this->app->make(MovementHandlerRegistry::class);
 * $registry->register(new CustomerLoanHandler());
 * ```
 */
class CustomerLoanHandler implements MovementHandlerInterface
{
    public function supports(string $movementType): bool
    {
        return in_array($movementType, ['customer_loan', 'loan_return'], true);
    }

    public function validate(Movement $movement, StockItem $stockItem): void
    {
        $referenceType = $movement->getReferenceType();

        if ($referenceType === 'customer_loan') {
            // Préstamo: validar stock disponible
            if ($stockItem->getAvailableQuantity() < $movement->getQuantity()) {
                throw new \DomainException(
                    "Stock insuficiente para préstamo. Disponible: {$stockItem->getAvailableQuantity()}, requerido: {$movement->getQuantity()}"
                );
            }

            // Validar metadata requerida
            $meta = $movement->getMeta();
            if (!isset($meta['customer_id'])) {
                throw new \DomainException('customer_id es requerido en meta para préstamos');
            }
        }

        if ($referenceType === 'loan_return') {
            // Devolución: solo validar que reference_id exista
            if (empty($movement->getReferenceId())) {
                throw new \DomainException('reference_id es requerido para devoluciones de préstamo');
            }
        }
    }

    public function handle(Movement $movement, StockItem $stockItem): StockItem
    {
        $quantity = $movement->getQuantity();
        $referenceType = $movement->getReferenceType();

        if ($referenceType === 'customer_loan') {
            // Préstamo: RESTA stock (inmutable - retorna nuevo objeto)
            return $stockItem->adjustQuantity(-$quantity);
        }

        if ($referenceType === 'loan_return') {
            // Devolución: SUMA stock
            return $stockItem->adjustQuantity($quantity);
        }

        return $stockItem;
    }

    public function describe(): string
    {
        return 'Customer Loan Handler: Maneja préstamos (customer_loan) y devoluciones (loan_return)';
    }
}
