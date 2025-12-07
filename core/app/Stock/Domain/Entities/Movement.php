<?php

declare(strict_types=1);

namespace App\Stock\Domain\Entities;

use App\Shared\Domain\Traits\HasId;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\ValueObjects\MovementType;
use DateTimeImmutable;

/**
 * Movement - Registro inmutable de movimiento de stock.
 * 
 * FUENTE DE VERDAD del sistema de inventario.
 * StockLevel se calcula agregando movements.
 * 
 * Principios:
 * - Inmutable una vez completado
 * - Sin datos redundantes calculables (balance se calcula)
 * - Referencias genéricas (source_type/id, reference_type/id)
 * - Tracking flexible (lot_id, tracked_unit_id)
 */
class Movement
{
    use HasId;

    public function __construct(
        private string $id,
        private MovementType $type,
        private string $itemId,
        private string $locationId,
        private float $quantity,
        private MovementStatus $status = MovementStatus::PENDING,
        
        // Tracking (nullable - si aplica)
        private ?string $lotId = null,
        private ?string $trackedUnitId = null,
        
        // Para transferencias
        private ?string $sourceLocationId = null,
        private ?string $destinationLocationId = null,
        
        // Origen genérico (supplier, production, warehouse, customer...)
        private ?string $sourceType = null,
        private ?string $sourceId = null,
        
        // Documento asociado (purchase_order, sales_order, return...)
        private ?string $referenceType = null,
        private ?string $referenceId = null,
        
        private ?string $reason = null,
        private ?string $performedBy = null,
        private ?string $workspaceId = null,
        private ?array $meta = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $processedAt = null
    ) {
        $this->setId($id);
        $this->quantity = abs($quantity);
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    // === Getters ===

    public function getType(): MovementType
    {
        return $this->type;
    }

    public function getStatus(): MovementStatus
    {
        return $this->status;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getLotId(): ?string
    {
        return $this->lotId;
    }

    public function getTrackedUnitId(): ?string
    {
        return $this->trackedUnitId;
    }

    public function getSourceLocationId(): ?string
    {
        return $this->sourceLocationId;
    }

    public function getDestinationLocationId(): ?string
    {
        return $this->destinationLocationId;
    }

    public function getSourceType(): ?string
    {
        return $this->sourceType;
    }

    public function getSourceId(): ?string
    {
        return $this->sourceId;
    }

    public function getReferenceType(): ?string
    {
        return $this->referenceType;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getPerformedBy(): ?string
    {
        return $this->performedBy;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspaceId;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    // === Domain Methods (reglas invariantes) ===

    /**
     * Delta efectivo para stock (positivo suma, negativo resta).
     */
    public function getEffectiveDelta(): float
    {
        return $this->quantity * $this->type->getQuantityMultiplier();
    }

    /**
     * Delta efectivo para reservas.
     */
    public function getReservationDelta(): float
    {
        return $this->quantity * $this->type->getReservationMultiplier();
    }

    public function affectsQuantity(): bool
    {
        return $this->type->addsStock() || $this->type->removesStock();
    }

    public function affectsReservation(): bool
    {
        return $this->type->affectsReservation();
    }

    public function isInbound(): bool
    {
        return $this->type->addsStock();
    }

    public function isOutbound(): bool
    {
        return $this->type->removesStock();
    }

    public function hasLot(): bool
    {
        return $this->lotId !== null;
    }

    public function hasTrackedUnit(): bool
    {
        return $this->trackedUnitId !== null;
    }

    public function hasTracking(): bool
    {
        return $this->lotId !== null || $this->trackedUnitId !== null;
    }

    public function hasSource(): bool
    {
        return $this->sourceType !== null && $this->sourceId !== null;
    }

    public function hasReference(): bool
    {
        return $this->referenceType !== null && $this->referenceId !== null;
    }

    public function canProcess(): bool
    {
        return $this->status->canProcess();
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function isTransfer(): bool
    {
        return $this->type === MovementType::TRANSFER_IN 
            || $this->type === MovementType::TRANSFER_OUT;
    }

    // === Status Transitions (inmutables) ===

    public function markAsCompleted(): self
    {
        $clone = clone $this;
        $clone->status = MovementStatus::COMPLETED;
        $clone->processedAt = new DateTimeImmutable();
        return $clone;
    }

    public function markAsCancelled(): self
    {
        $clone = clone $this;
        $clone->status = MovementStatus::CANCELLED;
        return $clone;
    }

    public function markAsFailed(): self
    {
        $clone = clone $this;
        $clone->status = MovementStatus::FAILED;
        return $clone;
    }

    // === Serialization ===

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => $this->quantity,
            'effective_delta' => $this->getEffectiveDelta(),
            'lot_id' => $this->lotId,
            'tracked_unit_id' => $this->trackedUnitId,
            'source_location_id' => $this->sourceLocationId,
            'destination_location_id' => $this->destinationLocationId,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'reason' => $this->reason,
            'performed_by' => $this->performedBy,
            'workspace_id' => $this->workspaceId,
            'meta' => $this->meta,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'processed_at' => $this->processedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
