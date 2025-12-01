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
        string $sku,
        string $locationId,
        int $quantity,
        ?string $lotNumber = null,
        ?DateTimeImmutable $expirationDate = null,
        ?string $referenceId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RECEIPT,
            sku: $sku,
            locationId: $locationId,
            quantity: $quantity,
            lotNumber: $lotNumber,
            expirationDate: $expirationDate,
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
        string $sku,
        string $locationId,
        int $quantity,
        ?string $lotNumber = null,
        ?string $referenceId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::SHIPMENT,
            sku: $sku,
            locationId: $locationId,
            quantity: $quantity,
            lotNumber: $lotNumber,
            referenceType: 'sales_order',
            referenceId: $referenceId,
            workspaceId: $workspaceId
        );
    }

    /**
     * Reserva de stock para un pedido.
     */
    public function createReservation(
        string $sku,
        string $locationId,
        int $quantity,
        ?string $referenceId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RESERVE,
            sku: $sku,
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
        string $sku,
        string $locationId,
        int $quantity,
        ?string $referenceId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RELEASE,
            sku: $sku,
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
        string $sku,
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
            sku: $sku,
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
        string $sku,
        string $sourceLocationId,
        string $destinationLocationId,
        int $quantity,
        ?string $lotNumber = null,
        ?string $transferId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::TRANSFER_OUT,
            sku: $sku,
            locationId: $sourceLocationId,
            quantity: $quantity,
            lotNumber: $lotNumber,
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
        string $sku,
        string $sourceLocationId,
        string $destinationLocationId,
        int $quantity,
        ?string $lotNumber = null,
        ?string $transferId = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::TRANSFER_IN,
            sku: $sku,
            locationId: $destinationLocationId,
            quantity: $quantity,
            lotNumber: $lotNumber,
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
        string $sku,
        string $locationId,
        int $countedQuantity,
        ?string $lotNumber = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::COUNT,
            sku: $sku,
            locationId: $locationId,
            quantity: $countedQuantity,
            lotNumber: $lotNumber,
            referenceType: 'inventory_count',
            reason: $reason,
            workspaceId: $workspaceId
        );
    }

    /**
     * Baja por vencimiento.
     */
    public function createExpiration(
        string $sku,
        string $locationId,
        int $quantity,
        string $lotNumber,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::EXPIRATION,
            sku: $sku,
            locationId: $locationId,
            quantity: $quantity,
            lotNumber: $lotNumber,
            referenceType: 'expiration',
            reason: $reason ?? 'Stock vencido',
            workspaceId: $workspaceId
        );
    }

    /**
     * Instalación (salida por servicio técnico).
     */
    public function createInstallation(
        string $sku,
        string $locationId,
        int $quantity,
        ?string $workOrderId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::INSTALLATION,
            sku: $sku,
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
        string $sku,
        string $locationId,
        int $quantity,
        ?string $returnOrderId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::RETURN,
            sku: $sku,
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
        string $sku,
        string $locationId,
        int $quantity,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: MovementType::DAMAGE,
            sku: $sku,
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
        string $sku,
        string $locationId,
        int $quantity,
        ?string $lotNumber = null,
        ?DateTimeImmutable $expirationDate = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $reason = null,
        ?string $workspaceId = null
    ): Movement {
        return new Movement(
            id: $this->idGenerator->generate(),
            type: $type,
            sku: $sku,
            locationId: $locationId,
            quantity: $quantity,
            lotNumber: $lotNumber,
            expirationDate: $expirationDate,
            referenceType: $referenceType,
            referenceId: $referenceId,
            reason: $reason,
            workspaceId: $workspaceId
        );
    }
}
