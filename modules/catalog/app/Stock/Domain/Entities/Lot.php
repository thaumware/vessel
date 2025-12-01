<?php

declare(strict_types=1);

namespace App\Stock\Domain\Entities;

use App\Shared\Domain\Traits\HasId;
use App\Stock\Domain\ValueObjects\LotStatus;
use DateTimeImmutable;

/**
 * Lot - Lote de productos con trazabilidad.
 * 
 * Un lote representa un grupo de unidades del mismo item que comparten
 * características comunes (origen, fecha producción, atributos de calidad).
 * 
 * Principios:
 * - Referencia a item_id del catálogo (no SKU directo)
 * - Origen genérico (source_type/source_id)
 * - Identificadores flexibles (lot_number, supplier_lot, batch_code...)
 * - Atributos dinámicos (expiration, production_date, quality_grade...)
 */
class Lot
{
    use HasId;

    public function __construct(
        private string $id,
        private string $itemId,
        private LotStatus $status = LotStatus::ACTIVE,
        
        // Identificadores flexibles (lot_number, supplier_lot, batch_code, etc.)
        private ?array $identifiers = null,
        
        // Atributos dinámicos (expiration_date, production_date, quality_grade, etc.)
        private ?array $attributes = null,
        
        // Origen genérico (supplier, production, warehouse...)
        private ?string $sourceType = null,
        private ?string $sourceId = null,
        
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

    public function getStatus(): LotStatus
    {
        return $this->status;
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

    public function getLotNumber(): ?string
    {
        return $this->getIdentifier('lot_number');
    }

    public function getSupplierLotNumber(): ?string
    {
        return $this->getIdentifier('supplier_lot');
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

    public function getExpirationDate(): ?DateTimeImmutable
    {
        $value = $this->getAttribute('expiration_date');
        return $value ? new DateTimeImmutable($value) : null;
    }

    public function getProductionDate(): ?DateTimeImmutable
    {
        $value = $this->getAttribute('production_date');
        return $value ? new DateTimeImmutable($value) : null;
    }

    public function getReceptionDate(): ?DateTimeImmutable
    {
        $value = $this->getAttribute('reception_date');
        return $value ? new DateTimeImmutable($value) : null;
    }

    public function getQualityGrade(): ?string
    {
        return $this->getAttribute('quality_grade');
    }

    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    // === Domain Methods ===

    public function hasExpiration(): bool
    {
        return $this->hasAttribute('expiration_date');
    }

    public function isExpired(): bool
    {
        $expiration = $this->getExpirationDate();
        return $expiration !== null && $expiration < new DateTimeImmutable();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        $expiration = $this->getExpirationDate();
        if ($expiration === null) {
            return false;
        }
        $threshold = (new DateTimeImmutable())->modify("+{$days} days");
        return $expiration <= $threshold && !$this->isExpired();
    }

    public function daysUntilExpiration(): ?int
    {
        $expiration = $this->getExpirationDate();
        if ($expiration === null) {
            return null;
        }
        $now = new DateTimeImmutable();
        $diff = $now->diff($expiration);
        return (int) $diff->format('%r%a');
    }

    public function isActive(): bool
    {
        return $this->status === LotStatus::ACTIVE;
    }

    public function isInQuarantine(): bool
    {
        return $this->status === LotStatus::QUARANTINE;
    }

    public function isUsable(): bool
    {
        return $this->isActive() && !$this->isExpired();
    }

    public function hasSource(): bool
    {
        return $this->sourceType !== null && $this->sourceId !== null;
    }

    public function getAgeInDays(): int
    {
        $referenceDate = $this->getReceptionDate() 
            ?? $this->getProductionDate() 
            ?? $this->createdAt;
        $now = new DateTimeImmutable();
        $diff = $referenceDate->diff($now);
        return (int) $diff->format('%a');
    }

    // === Status Transitions ===

    public function activate(): self
    {
        $clone = clone $this;
        $clone->status = LotStatus::ACTIVE;
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function quarantine(): self
    {
        $clone = clone $this;
        $clone->status = LotStatus::QUARANTINE;
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function markAsExpired(): self
    {
        $clone = clone $this;
        $clone->status = LotStatus::EXPIRED;
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function markAsDepleted(): self
    {
        $clone = clone $this;
        $clone->status = LotStatus::DEPLETED;
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    // === Mutations ===

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

    // === Factory Methods ===

    public static function create(
        string $id,
        string $itemId,
        ?string $lotNumber = null,
        ?DateTimeImmutable $expirationDate = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
        ?string $workspaceId = null
    ): self {
        $identifiers = $lotNumber ? ['lot_number' => $lotNumber] : null;
        $attributes = $expirationDate 
            ? ['expiration_date' => $expirationDate->format('Y-m-d'), 'reception_date' => (new DateTimeImmutable())->format('Y-m-d')]
            : ['reception_date' => (new DateTimeImmutable())->format('Y-m-d')];

        return new self(
            id: $id,
            itemId: $itemId,
            identifiers: $identifiers,
            attributes: $attributes,
            sourceType: $sourceType,
            sourceId: $sourceId,
            workspaceId: $workspaceId
        );
    }

    // === Serialization ===

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'item_id' => $this->itemId,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'identifiers' => $this->identifiers,
            'lot_number' => $this->getLotNumber(),
            'attributes' => $this->attributes,
            'expiration_date' => $this->getExpirationDate()?->format('Y-m-d'),
            'production_date' => $this->getProductionDate()?->format('Y-m-d'),
            'reception_date' => $this->getReceptionDate()?->format('Y-m-d'),
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'is_expired' => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'days_until_expiration' => $this->daysUntilExpiration(),
            'age_in_days' => $this->getAgeInDays(),
            'workspace_id' => $this->workspaceId,
            'meta' => $this->meta,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
