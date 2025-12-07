<?php

declare(strict_types=1);

namespace App\Stock\Domain\Entities;

use DateTimeImmutable;

/**
 * StockLevel - Vista calculada del stock actual por item+location.
 * 
 * CACHE del estado actual del inventario.
 * Se calcula agregando todos los Movement del item+location.
 * 
 * Principios:
 * - NO es fuente de verdad (Movement lo es)
 * - Se puede recalcular en cualquier momento
 * - Optimización para consultas frecuentes
 * - Sin ID propio (clave compuesta item_id+location_id)
 */
class StockLevel
{
    public function __construct(
        private string $itemId,
        private string $locationId,
        private float $quantity = 0,
        private float $reservedQuantity = 0,
        private ?string $lastMovementId = null,
        private ?string $workspaceId = null,
        private ?DateTimeImmutable $calculatedAt = null
    ) {
        $this->calculatedAt = $calculatedAt ?? new DateTimeImmutable();
    }

    // === Getters ===

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

    public function getReservedQuantity(): float
    {
        return $this->reservedQuantity;
    }

    public function getLastMovementId(): ?string
    {
        return $this->lastMovementId;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspaceId;
    }

    public function getCalculatedAt(): DateTimeImmutable
    {
        return $this->calculatedAt;
    }

    // === Computed ===

    public function getAvailableQuantity(): float
    {
        return max(0, $this->quantity - $this->reservedQuantity);
    }

    public function hasStock(): bool
    {
        return $this->quantity > 0;
    }

    public function hasAvailable(float $needed = 1): bool
    {
        return $this->getAvailableQuantity() >= $needed;
    }

    public function isEmpty(): bool
    {
        return $this->quantity <= 0;
    }

    public function isFullyReserved(): bool
    {
        return $this->quantity > 0 && $this->getAvailableQuantity() <= 0;
    }

    // === Key ===

    public function getCompositeKey(): string
    {
        return "{$this->itemId}:{$this->locationId}";
    }

    public static function makeKey(string $itemId, string $locationId): string
    {
        return "{$itemId}:{$locationId}";
    }

    // === Mutations ===

    public function withQuantityDelta(float $delta, string $movementId): self
    {
        return new self(
            $this->itemId,
            $this->locationId,
            $this->quantity + $delta,
            $this->reservedQuantity,
            $movementId,
            $this->workspaceId,
            new DateTimeImmutable()
        );
    }

    public function withReservationDelta(float $delta, string $movementId): self
    {
        return new self(
            $this->itemId,
            $this->locationId,
            $this->quantity,
            max(0, $this->reservedQuantity + $delta),
            $movementId,
            $this->workspaceId,
            new DateTimeImmutable()
        );
    }

    public function recalculate(float $quantity, float $reserved, ?string $lastMovementId): self
    {
        return new self(
            $this->itemId,
            $this->locationId,
            $quantity,
            $reserved,
            $lastMovementId,
            $this->workspaceId,
            new DateTimeImmutable()
        );
    }

    // === Factory ===

    public static function empty(string $itemId, string $locationId, ?string $workspaceId = null): self
    {
        return new self($itemId, $locationId, 0, 0, null, $workspaceId);
    }

    // === Serialization ===

    public function toArray(): array
    {
        return [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => $this->quantity,
            'reserved_quantity' => $this->reservedQuantity,
            'available_quantity' => $this->getAvailableQuantity(),
            'last_movement_id' => $this->lastMovementId,
            'workspace_id' => $this->workspaceId,
            'calculated_at' => $this->calculatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
