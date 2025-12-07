<?php

declare(strict_types=1);

namespace App\Stock\Domain\Entities;

use App\Shared\Domain\Traits\HasId;
use App\Stock\Domain\ValueObjects\TrackingStatus;
use DateTimeImmutable;

/**
 * TrackedUnit - Unidad individual con número de serie u otros identificadores únicos.
 * 
 * Representa UNA unidad física que se rastrea individualmente.
 * Ejemplos: equipos electrónicos, maquinaria, activos fijos.
 * 
 * Principios:
 * - Cada unidad tiene identidad única
 * - Identificadores flexibles (serial, imei, mac, asset_tag...)
 * - Atributos extensibles (warranty, color, condition...)
 * - Trazabilidad completa del origen
 */
class TrackedUnit
{
    use HasId;

    public function __construct(
        private string $id,
        private string $itemId,
        private TrackingStatus $status = TrackingStatus::AVAILABLE,
        
        // Puede pertenecer a un lote
        private ?string $lotId = null,
        
        // Ubicación actual
        private ?string $locationId = null,
        
        // Identificadores flexibles (serial_number, imei, mac_address, asset_tag, vin, etc.)
        private ?array $identifiers = null,
        
        // Atributos dinámicos (warranty_end, color, condition, grade, etc.)
        private ?array $attributes = null,
        
        // Origen genérico
        private ?string $sourceType = null,
        private ?string $sourceId = null,
        
        // Asignación actual (customer, employee, project...)
        private ?string $assignedToType = null,
        private ?string $assignedToId = null,
        
        private ?string $workspaceId = null,
        private ?array $meta = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null
    ) {
        $this->setId($id);
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    // === Getters ===

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getStatus(): TrackingStatus
    {
        return $this->status;
    }

    public function getLotId(): ?string
    {
        return $this->lotId;
    }

    public function getLocationId(): ?string
    {
        return $this->locationId;
    }

    public function getIdentifiers(): ?array
    {
        return $this->identifiers;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function getSourceType(): ?string
    {
        return $this->sourceType;
    }

    public function getSourceId(): ?string
    {
        return $this->sourceId;
    }

    public function getAssignedToType(): ?string
    {
        return $this->assignedToType;
    }

    public function getAssignedToId(): ?string
    {
        return $this->assignedToId;
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

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // === Identifier Helpers ===

    public function getIdentifier(string $key): ?string
    {
        return $this->identifiers[$key] ?? null;
    }

    public function getSerialNumber(): ?string
    {
        return $this->getIdentifier('serial_number');
    }

    public function getImei(): ?string
    {
        return $this->getIdentifier('imei');
    }

    public function getMacAddress(): ?string
    {
        return $this->getIdentifier('mac_address');
    }

    public function getAssetTag(): ?string
    {
        return $this->getIdentifier('asset_tag');
    }

    public function hasIdentifier(string $key): bool
    {
        return isset($this->identifiers[$key]);
    }

    // === Attribute Helpers ===

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function getWarrantyEnd(): ?DateTimeImmutable
    {
        $value = $this->getAttribute('warranty_end');
        return $value ? new DateTimeImmutable($value) : null;
    }

    public function getCondition(): ?string
    {
        return $this->getAttribute('condition');
    }

    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    // === Domain Methods ===

    public function isAvailable(): bool
    {
        return $this->status === TrackingStatus::AVAILABLE;
    }

    public function isAssigned(): bool
    {
        return $this->assignedToType !== null && $this->assignedToId !== null;
    }

    public function hasSource(): bool
    {
        return $this->sourceType !== null && $this->sourceId !== null;
    }

    public function belongsToLot(): bool
    {
        return $this->lotId !== null;
    }

    public function isInWarranty(): bool
    {
        $warrantyEnd = $this->getWarrantyEnd();
        if ($warrantyEnd === null) {
            return false;
        }
        return $warrantyEnd > new DateTimeImmutable();
    }

    // === Mutations (inmutable) ===

    public function withStatus(TrackingStatus $status): self
    {
        $clone = clone $this;
        $clone->status = $status;
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function withLocation(string $locationId): self
    {
        $clone = clone $this;
        $clone->locationId = $locationId;
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function withAssignment(?string $type, ?string $id): self
    {
        $clone = clone $this;
        $clone->assignedToType = $type;
        $clone->assignedToId = $id;
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function withIdentifier(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->identifiers = array_merge($clone->identifiers ?? [], [$key => $value]);
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function withAttribute(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes = array_merge($clone->attributes ?? [], [$key => $value]);
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    // === Serialization ===

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'item_id' => $this->itemId,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'lot_id' => $this->lotId,
            'location_id' => $this->locationId,
            'identifiers' => $this->identifiers,
            'attributes' => $this->attributes,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'assigned_to_type' => $this->assignedToType,
            'assigned_to_id' => $this->assignedToId,
            'workspace_id' => $this->workspaceId,
            'meta' => $this->meta,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
