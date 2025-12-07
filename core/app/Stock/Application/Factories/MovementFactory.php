<?php

declare(strict_types=1);

namespace App\Stock\Application\Factories;

use App\Shared\Domain\Interfaces\IdGeneratorInterface;
use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementStatus;
use DateTimeImmutable;

/**
 * Factory para crear movimientos de stock.
 * 
 * Esta clase pertenece a la capa de Application porque:
 * - Conoce los casos de uso específicos del negocio
 * - Define referenceTypes específicos de la aplicación
 * - Implementa lógica de creación que no es invariante del dominio
 * 
 * Nota: Los parámetros usan 'itemId' (nuevo) pero también aceptan 'sku' como alias
 * para mantener compatibilidad hacia atrás.
 */
class MovementFactory
{
    public function __construct(
        private IdGeneratorInterface $idGenerator
    ) {
    }

    /**
     * Recepción de mercadería (compra, ingreso).
     */
    public function createReceipt(
        string $itemId,
        string $locationId,
        int $quantity,
        ?string $lotId = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
        ?string $referenceId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RECEIPT,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            lotId: $lotId,
            sourceType: $sourceType,
            sourceId: $sourceId,
            referenceType: 'purchase_order',
            referenceId: $referenceId,
            reason: $reason,
            workspaceId: $workspaceId
        );
    }

    /**
     * Despacho/envío (venta, salida).
     */
    public function createShipment(
        string $itemId,
        string $locationId,
        int $quantity,
        ?string $lotId = null,
        ?string $referenceId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::SHIPMENT,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            lotId: $lotId,
            referenceType: 'sales_order',
            referenceId: $referenceId,
            workspaceId: $workspaceId
        );
    }

    /**
     * Reserva de stock para un pedido.
     */
    public function createReservation(
        string $itemId,
        string $locationId,
        int $quantity,
        ?string $referenceId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RESERVE,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            referenceType: 'sales_order',
            referenceId: $referenceId,
            workspaceId: $workspaceId
        );
    }

    /**
     * Liberación de reserva.
     */
    public function createRelease(
        string $itemId,
        string $locationId,
        int $quantity,
        ?string $referenceId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RELEASE,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            referenceType: 'sales_order',
            referenceId: $referenceId,
            workspaceId: $workspaceId
        );
    }

    /**
     * Ajuste de inventario (positivo o negativo).
     */
    public function createAdjustment(
        string $itemId,
        string $locationId,
        int $delta,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        $type = $delta >= 0 
            ? MovementType::ADJUSTMENT_IN 
            : MovementType::ADJUSTMENT_OUT;
        
        return new Movement(
            id: $this->idGenerator->generate(),
            type: $type,
            itemId: $itemId,
            locationId: $locationId,
            quantity: abs($delta),
            referenceType: 'inventory_adjustment',
            reason: $reason,
            workspaceId: $workspaceId
        );
    }

    /**
     * Transferencia entre ubicaciones (genera salida).
     */
    public function createTransferOut(
        string $itemId,
        string $sourceLocationId,
        string $destinationLocationId,
        int $quantity,
        ?string $lotId = null,
        ?string $transferId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::TRANSFER_OUT,
            itemId: $itemId,
            locationId: $sourceLocationId,
            quantity: $quantity,
            lotId: $lotId,
            sourceLocationId: $sourceLocationId,
            destinationLocationId: $destinationLocationId,
            referenceType: 'transfer',
            referenceId: $transferId,
            workspaceId: $workspaceId
        );
    }

    /**
     * Transferencia entre ubicaciones (genera entrada).
     */
    public function createTransferIn(
        string $itemId,
        string $sourceLocationId,
        string $destinationLocationId,
        int $quantity,
        ?string $lotId = null,
        ?string $transferId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::TRANSFER_IN,
            itemId: $itemId,
            locationId: $destinationLocationId,
            quantity: $quantity,
            lotId: $lotId,
            sourceLocationId: $sourceLocationId,
            destinationLocationId: $destinationLocationId,
            referenceType: 'transfer',
            referenceId: $transferId,
            workspaceId: $workspaceId
        );
    }

    /**
     * Conteo de inventario (no afecta stock, solo registro).
     */
    public function createCount(
        string $itemId,
        string $locationId,
        int $countedQuantity,
        ?string $lotId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::COUNT,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $countedQuantity,
            lotId: $lotId,
            referenceType: 'inventory_count',
            reason: $reason,
            workspaceId: $workspaceId
        );
    }

    /**
     * Baja por vencimiento.
     */
    public function createExpiration(
        string $itemId,
        string $locationId,
        int $quantity,
        string $lotId,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::EXPIRATION,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            lotId: $lotId,
            referenceType: 'expiration',
            reason: $reason ?? 'Stock vencido',
            workspaceId: $workspaceId
        );
    }

    /**
     * Instalación (salida por servicio técnico).
     */
    public function createInstallation(
        string $itemId,
        string $locationId,
        int $quantity,
        ?string $workOrderId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::INSTALLATION,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            referenceType: 'work_order',
            referenceId: $workOrderId,
            reason: $reason ?? 'Instalación en cliente',
            workspaceId: $workspaceId
        );
    }

    /**
     * Devolución de cliente (entrada).
     */
    public function createCustomerReturn(
        string $itemId,
        string $locationId,
        int $quantity,
        ?string $returnOrderId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RETURN,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            referenceType: 'return_order',
            referenceId: $returnOrderId,
            reason: $reason ?? 'Devolución de cliente',
            workspaceId: $workspaceId
        );
    }

    /**
     * Baja por daño/merma.
     */
    public function createDamage(
        string $itemId,
        string $locationId,
        int $quantity,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::DAMAGE,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            referenceType: 'damage_report',
            reason: $reason ?? 'Producto dañado',
            workspaceId: $workspaceId
        );
    }

    /**
     * Crea movimiento genérico desde un tipo.
     */
    public function create(
        MovementType $type,
        string $itemId,
        string $locationId,
        int $quantity,
        ?string $lotId = null,
        ?string $trackedUnitId = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: $type,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            lotId: $lotId,
            trackedUnitId: $trackedUnitId,
            sourceType: $sourceType,
            sourceId: $sourceId,
            referenceType: $referenceType,
            referenceId: $referenceId,
            reason: $reason,
            workspaceId: $workspaceId
        );
    }
}
