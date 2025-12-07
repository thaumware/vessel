<?php

namespace App\Stock\Domain\Entities;

use App\Shared\Domain\Traits\HasId;
use DateTimeImmutable;

/**
 * StockItem - Representa la existencia física de un item del catálogo.
 * 
 * Se conecta al catálogo (interno o externo) mediante:
 * - itemId: ID canónico del catálogo (requerido)
 * - catalogItemId: ID externo/ERP (opcional)
 * - catalogOrigin: Origen del catálogo (internal, external_erp, etc.)
 * 
 * El catálogo define los atributos del producto (nombre, descripción, categorías).
 * StockItem maneja la existencia real (cantidad, ubicación, lotes, etc.).
 */
class StockItem
{
    use HasId;

    public function __construct(
        private string $id,
        private string $itemId,
        private string $locationId,
        private ?string $catalogItemId = null,
        private ?string $catalogOrigin = null,
        private ?string $locationType = null,
        private float $quantity = 0,
        private float $reservedQuantity = 0,
        private ?string $lotNumber = null,
        private ?DateTimeImmutable $expirationDate = null,
        private ?string $serialNumber = null,
        private ?string $workspaceId = null,
        private ?array $meta = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
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

    /**
     * @deprecated usar getItemId(); se mantiene por compatibilidad
     */
    public function getSku(): string
    {
        return $this->itemId;
    }

    public function getCatalogItemId(): ?string
    {
        return $this->catalogItemId;
    }

    public function getCatalogOrigin(): ?string
    {
        return $this->catalogOrigin;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getLocationType(): ?string
    {
        return $this->locationType;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getReservedQuantity(): float
    {
        return $this->reservedQuantity;
    }

    public function getAvailableQuantity(): float
    {
        return $this->quantity - $this->reservedQuantity;
    }

    public function getLotNumber(): ?string
    {
        return $this->lotNumber;
    }

    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
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

    public function isExpired(): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }
        return $this->expirationDate < new DateTimeImmutable();
    }

    public function hasAvailableStock(float $quantity = 1): bool
    {
        return $this->getAvailableQuantity() >= $quantity;
    }

    public function isLotTracked(): bool
    {
        return $this->lotNumber !== null;
    }

    public function isSerialTracked(): bool
    {
        return $this->serialNumber !== null;
    }

    // === Mutation Methods (return new instance for immutability) ===

    public function withQuantity(float $quantity): self
    {
        return new self(
            $this->id,
            $this->itemId,
            $this->locationId,
            $this->catalogItemId,
            $this->catalogOrigin,
            $this->locationType,
            $quantity,
            $this->reservedQuantity,
            $this->lotNumber,
            $this->expirationDate,
            $this->serialNumber,
            $this->workspaceId,
            $this->meta,
            $this->createdAt,
            new DateTimeImmutable(),
        );
    }

    public function withReservedQuantity(float $reservedQuantity): self
    {
        return new self(
            $this->id,
            $this->itemId,
            $this->locationId,
            $this->catalogItemId,
            $this->catalogOrigin,
            $this->locationType,
            $this->quantity,
            $reservedQuantity,
            $this->lotNumber,
            $this->expirationDate,
            $this->serialNumber,
            $this->workspaceId,
            $this->meta,
            $this->createdAt,
            new DateTimeImmutable(),
        );
    }

    public function adjustQuantity(float $delta): self
    {
        return $this->withQuantity($this->quantity + $delta);
    }

    public function reserve(float $quantity): self
    {
        // Permite reservar más de lo disponible (stock negativo disponible)
        // La validación de negocio debe hacerse en la capa de aplicación si se requiere
        return $this->withReservedQuantity($this->reservedQuantity + $quantity);
    }

    public function release(float $quantity): self
    {
        // Permite liberar incluso si resulta en reserva negativa
        // La validación de negocio debe hacerse en la capa de aplicación si se requiere
        return $this->withReservedQuantity($this->reservedQuantity - $quantity);
    }

    // === Serialization ===

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'item_id' => $this->itemId,
            'sku' => $this->itemId, // alias legado
            'catalog_item_id' => $this->catalogItemId,
            'catalog_origin' => $this->catalogOrigin,
            'location_id' => $this->locationId,
            'location_type' => $this->locationType,
            'quantity' => $this->quantity,
            'reserved_quantity' => $this->reservedQuantity,
            'available_quantity' => $this->getAvailableQuantity(),
            'lot_number' => $this->lotNumber,
            'expiration_date' => $this->expirationDate?->format('Y-m-d'),
            'serial_number' => $this->serialNumber,
            'workspace_id' => $this->workspaceId,
            'meta' => $this->meta,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
