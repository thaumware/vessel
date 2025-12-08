<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Handlers;

use App\Stock\Domain\Interfaces\MovementHandlerInterface;
use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;

/**
 * Handler EJEMPLO para movimientos de consignación.
 * 
 * Tipo: 'consignment_out' y 'consignment_return'
 * 
 * LÓGICA ESPECIAL:
 * - consignment_out: RESTA stock, pero NO reserva (diferente a shipment)
 * - consignment_return: SUMA stock si no se vendió
 * - Se puede agregar meta['sold'] = true para indicar que se vendió
 * 
 * USO:
 * ```php
 * // Enviar en consignación
 * POST /api/v1/stock/movements
 * {
 *   "type": "custom",
 *   "item_id": "ITEM-001",
 *   "location_id": "WAREHOUSE-MAIN",
 *   "quantity": 10,
 *   "reference_type": "consignment_out",
 *   "reference_id": "CONSIGN-2024-001",
 *   "meta": {
 *     "consignee": "RETAIL-STORE-001",
 *     "agreement_expires": "2025-01-31"
 *   }
 * }
 * 
 * // Devolver consignación (no se vendió)
 * POST /api/v1/stock/movements
 * {
 *   "type": "custom",
 *   "item_id": "ITEM-001",
 *   "location_id": "WAREHOUSE-MAIN",
 *   "quantity": 3,
 *   "reference_type": "consignment_return",
 *   "reference_id": "CONSIGN-2024-001",
 *   "meta": {
 *     "sold": false,
 *     "reason": "No se vendió en tienda"
 *   }
 * }
 * ```
 */
class ConsignmentHandler implements MovementHandlerInterface
{
    public function supports(string $movementType): bool
    {
        return in_array($movementType, ['consignment_out', 'consignment_return'], true);
    }

    public function validate(Movement $movement, StockItem $stockItem): void
    {
        $referenceType = $movement->getReferenceType();

        if ($referenceType === 'consignment_out') {
            // Validar stock suficiente
            if ($stockItem->getQuantity() < $movement->getQuantity()) {
                throw new \DomainException(
                    "Stock insuficiente para consignación. Stock: {$stockItem->getQuantity()}, requerido: {$movement->getQuantity()}"
                );
            }

            // Validar metadata
            $meta = $movement->getMeta();
            if (!isset($meta['consignee'])) {
                throw new \DomainException('consignee es requerido en meta para consignación');
            }
        }

        if ($referenceType === 'consignment_return') {
            // Validar que no esté marcado como vendido
            $meta = $movement->getMeta();
            if (isset($meta['sold']) && $meta['sold'] === true) {
                throw new \DomainException('No se puede devolver consignación marcada como vendida');
            }
        }
    }

    public function handle(Movement $movement, StockItem $stockItem): StockItem
    {
        $quantity = $movement->getQuantity();
        $referenceType = $movement->getReferenceType();

        if ($referenceType === 'consignment_out') {
            // Salida en consignación: RESTA stock
            return $stockItem->adjustQuantity(-$quantity);
        }

        if ($referenceType === 'consignment_return') {
            // Devolución de consignación: SUMA stock
            return $stockItem->adjustQuantity($quantity);
        }

        return $stockItem;
    }

    public function describe(): string
    {
        return 'Consignment Handler: Maneja envíos en consignación (consignment_out) y devoluciones (consignment_return)';
    }
}
