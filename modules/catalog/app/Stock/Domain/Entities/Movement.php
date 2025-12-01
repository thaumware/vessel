<?php

declare(strict_types=1);

namespace App\Stock\Domain\Entities;

use App\Shared\Domain\Traits\HasId;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\ValueObjects\MovementType;
use DateTimeImmutable;

/**
 * Movement - Registro de movimiento de stock.
 * 
 * Entidad pura de dominio que representa un movimiento.
 * El tipo de movimiento (MovementType) determina cómo se afectan las cantidades.
 * 
 * Para crear movimientos específicos del negocio, usar MovementFactory en Application.
 */
class Movement
{
    use HasId;

    public function __construct(
        private string $id,
        private MovementType $type,
        private string $sku,
        private string $locationId,
        private int $quantity,
        private MovementStatus $status = MovementStatus::PENDING,
        private ?string $lotNumber = null,
        private ?DateTimeImmutable $expirationDate = null,
        private ?string $sourceLocationId = null,
        private ?string $destinationLocationId = null,
        private ?string $referenceType = null,
        private ?string $referenceId = null,
        private ?string $reason = null,
        private ?int $balanceAfter = null,
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

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getLotNumber(): ?string
    {
        return $this->lotNumber;
    }

    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function getSourceLocationId(): ?string
    {
        return $this->sourceLocationId;
    }

    public function getDestinationLocationId(): ?string
    {
        return $this->destinationLocationId;
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

    public function getBalanceAfter(): ?int
    {
        return $this->balanceAfter;
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
    public function getEffectiveDelta(): int
    {
        return $this->quantity * $this->type->getQuantityMultiplier();
    }

    /**
     * Delta efectivo para reservas.
     */
    public function getReservationDelta(): int
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
        return $this->lotNumber !== null;
    }

    public function hasExpiration(): bool
    {
        return $this->expirationDate !== null;
    }

    public function isExpired(): bool
    {
        return $this->expirationDate !== null 
            && $this->expirationDate < new DateTimeImmutable();
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

    public function withBalanceAfter(int $balance): self
    {
        $clone = clone $this;
        $clone->balanceAfter = $balance;
        return $clone;
    }

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
            'sku' => $this->sku,
            'location_id' => $this->locationId,
            'quantity' => $this->quantity,
            'effective_delta' => $this->getEffectiveDelta(),
            'lot_number' => $this->lotNumber,
            'expiration_date' => $this->expirationDate?->format('Y-m-d'),
            'source_location_id' => $this->sourceLocationId,
            'destination_location_id' => $this->destinationLocationId,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'reason' => $this->reason,
            'balance_after' => $this->balanceAfter,
            'performed_by' => $this->performedBy,
            'workspace_id' => $this->workspaceId,
            'meta' => $this->meta,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'processed_at' => $this->processedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
