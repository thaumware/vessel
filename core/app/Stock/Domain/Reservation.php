<?php

declare(strict_types=1);

namespace App\Stock\Domain;

use DateTimeImmutable;

/**
 * Entidad ligera para tracking de reservas
 * No es parte del aggregate principal, solo para consulta del frontend
 */
final class Reservation
{
    public function __construct(
        private string $id,
        private string $itemId,
        private string $locationId,
        private float $quantity,
        private string $reservedBy, // user-id, system, etc
        private string $referenceType, // order, project, loan, etc
        private string $referenceId,
        private ReservationStatus $status,
        private ?DateTimeImmutable $expiresAt = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $releasedAt = null,
    ) {
        $this->createdAt ??= new DateTimeImmutable();
    }

    public static function create(
        string $id,
        string $itemId,
        string $locationId,
        float $quantity,
        string $reservedBy,
        string $referenceType,
        string $referenceId,
        ?DateTimeImmutable $expiresAt = null,
    ): self {
        return new self(
            id: $id,
            itemId: $itemId,
            locationId: $locationId,
            quantity: $quantity,
            reservedBy: $reservedBy,
            referenceType: $referenceType,
            referenceId: $referenceId,
            status: ReservationStatus::ACTIVE,
            expiresAt: $expiresAt,
        );
    }

    public function release(): self
    {
        return new self(
            id: $this->id,
            itemId: $this->itemId,
            locationId: $this->locationId,
            quantity: $this->quantity,
            reservedBy: $this->reservedBy,
            referenceType: $this->referenceType,
            referenceId: $this->referenceId,
            status: ReservationStatus::RELEASED,
            expiresAt: $this->expiresAt,
            createdAt: $this->createdAt,
            releasedAt: new DateTimeImmutable(),
        );
    }

    public function expire(): self
    {
        return new self(
            id: $this->id,
            itemId: $this->itemId,
            locationId: $this->locationId,
            quantity: $this->quantity,
            reservedBy: $this->reservedBy,
            referenceType: $this->referenceType,
            referenceId: $this->referenceId,
            status: ReservationStatus::EXPIRED,
            expiresAt: $this->expiresAt,
            createdAt: $this->createdAt,
            releasedAt: new DateTimeImmutable(),
        );
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt < new DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->status === ReservationStatus::ACTIVE && !$this->isExpired();
    }

    // Getters

    public function getId(): string
    {
        return $this->id;
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

    public function getReservedBy(): string
    {
        return $this->reservedBy;
    }

    public function getReferenceType(): string
    {
        return $this->referenceType;
    }

    public function getReferenceId(): string
    {
        return $this->referenceId;
    }

    public function getStatus(): ReservationStatus
    {
        return $this->status;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReleasedAt(): ?DateTimeImmutable
    {
        return $this->releasedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => $this->quantity,
            'reserved_by' => $this->reservedBy,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'status' => $this->status->value,
            'expires_at' => $this->expiresAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'released_at' => $this->releasedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
