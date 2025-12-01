<?php

declare(strict_types=1);

namespace App\Stock\Domain\Entities;

use App\Shared\Domain\Traits\HasId;
use DateTimeImmutable;

/**
 * Lot - Lote de producto con trazabilidad y vencimiento.
 * 
 * Un lote representa un grupo de unidades del mismo SKU que comparten:
 * - Fecha de producción/recepción
 * - Fecha de vencimiento
 * - Proveedor/origen
 * - Características de calidad
 */
class Lot
{
    use HasId;

    public function __construct(
        private string $id,
        private string $lotNumber,
        private string $sku,
        private ?DateTimeImmutable $expirationDate = null,
        private ?DateTimeImmutable $productionDate = null,
        private ?DateTimeImmutable $receptionDate = null,
        private ?string $supplierId = null,
        private ?string $supplierLotNumber = null,
        private ?string $status = 'active', // active, quarantine, expired, depleted
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

    public function getLotNumber(): string
    {
        return $this->lotNumber;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function getProductionDate(): ?DateTimeImmutable
    {
        return $this->productionDate;
    }

    public function getReceptionDate(): ?DateTimeImmutable
    {
        return $this->receptionDate;
    }

    public function getSupplierId(): ?string
    {
        return $this->supplierId;
    }

    public function getSupplierLotNumber(): ?string
    {
        return $this->supplierLotNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
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

    // === Domain Methods ===

    /**
     * ¿Tiene fecha de vencimiento?
     */
    public function hasExpiration(): bool
    {
        return $this->expirationDate !== null;
    }

    /**
     * ¿Está vencido?
     */
    public function isExpired(): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }
        return $this->expirationDate < new DateTimeImmutable();
    }

    /**
     * ¿Está próximo a vencer? (dentro de X días)
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }
        $threshold = (new DateTimeImmutable())->modify("+{$days} days");
        return $this->expirationDate <= $threshold && !$this->isExpired();
    }

    /**
     * Días hasta vencimiento (negativo si ya venció).
     */
    public function daysUntilExpiration(): ?int
    {
        if ($this->expirationDate === null) {
            return null;
        }
        $now = new DateTimeImmutable();
        $diff = $now->diff($this->expirationDate);
        $days = (int) $diff->format('%r%a');
        return $days;
    }

    /**
     * ¿Está activo?
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * ¿Está en cuarentena?
     */
    public function isInQuarantine(): bool
    {
        return $this->status === 'quarantine';
    }

    /**
     * ¿Puede usarse? (activo y no vencido)
     */
    public function isUsable(): bool
    {
        return $this->isActive() && !$this->isExpired();
    }

    /**
     * Edad del lote en días desde recepción.
     */
    public function getAgeInDays(): ?int
    {
        $referenceDate = $this->receptionDate ?? $this->productionDate ?? $this->createdAt;
        $now = new DateTimeImmutable();
        $diff = $referenceDate->diff($now);
        return (int) $diff->format('%a');
    }

    // === Status Transitions ===

    public function activate(): self
    {
        $clone = clone $this;
        $clone->status = 'active';
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function quarantine(): self
    {
        $clone = clone $this;
        $clone->status = 'quarantine';
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function markAsExpired(): self
    {
        $clone = clone $this;
        $clone->status = 'expired';
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    public function markAsDepleted(): self
    {
        $clone = clone $this;
        $clone->status = 'depleted';
        $clone->updatedAt = new DateTimeImmutable();
        return $clone;
    }

    // === Factory Methods ===

    public static function create(
        string $id,
        string $lotNumber,
        string $sku,
        ?DateTimeImmutable $expirationDate = null,
        ?string $supplierId = null,
        ?string $workspaceId = null
    ): self {
        return new self(
            id: $id,
            lotNumber: $lotNumber,
            sku: $sku,
            expirationDate: $expirationDate,
            receptionDate: new DateTimeImmutable(),
            supplierId: $supplierId,
            workspaceId: $workspaceId
        );
    }

    // === Serialization ===

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'lot_number' => $this->lotNumber,
            'sku' => $this->sku,
            'expiration_date' => $this->expirationDate?->format('Y-m-d'),
            'production_date' => $this->productionDate?->format('Y-m-d'),
            'reception_date' => $this->receptionDate?->format('Y-m-d'),
            'supplier_id' => $this->supplierId,
            'supplier_lot_number' => $this->supplierLotNumber,
            'status' => $this->status,
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
