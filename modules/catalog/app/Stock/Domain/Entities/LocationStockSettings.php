<?php

declare(strict_types=1);

namespace App\Stock\Domain\Entities;

use App\Shared\Domain\Traits\HasId;
use DateTimeImmutable;

/**
 * LocationStockSettings - Configuracion de stock para una ubicacion.
 * 
 * Define restricciones de capacidad que el modulo Stock usa para validar
 * movimientos. La ubicacion en si es definida por el modulo Locations,
 * pero las restricciones de inventario son responsabilidad de Stock.
 * 
 * Ejemplos de uso:
 * - max_quantity: Capacidad maxima total de unidades en la ubicacion
 * - max_weight: Peso maximo permitido (kg)
 * - max_volume: Volumen maximo permitido (m3)
 * - allowed_item_types: Tipos de items permitidos (ej: solo "hazmat", "cold_chain")
 * - allow_mixed_lots: Si permite mezclar lotes diferentes del mismo SKU
 * - allow_mixed_skus: Si permite diferentes SKUs en la misma ubicacion
 * - fifo_enforced: Si requiere FIFO estricto
 */
class LocationStockSettings
{
    use HasId;

    public function __construct(
        private string $id,
        private string $locationId,
        private ?int $maxQuantity = null,
        private ?float $maxWeight = null,
        private ?float $maxVolume = null,
        private ?array $allowedItemTypes = null,
        private bool $allowMixedLots = true,
        private bool $allowMixedSkus = true,
        private bool $fifoEnforced = false,
        private bool $isActive = true,
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

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getMaxQuantity(): ?int
    {
        return $this->maxQuantity;
    }

    public function getMaxWeight(): ?float
    {
        return $this->maxWeight;
    }

    public function getMaxVolume(): ?float
    {
        return $this->maxVolume;
    }

    public function getAllowedItemTypes(): ?array
    {
        return $this->allowedItemTypes;
    }

    public function allowsMixedLots(): bool
    {
        return $this->allowMixedLots;
    }

    public function allowsMixedSkus(): bool
    {
        return $this->allowMixedSkus;
    }

    public function isFifoEnforced(): bool
    {
        return $this->fifoEnforced;
    }

    public function isActive(): bool
    {
        return $this->isActive;
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
     * Verifica si tiene restriccion de capacidad
     */
    public function hasCapacityLimit(): bool
    {
        return $this->maxQuantity !== null 
            || $this->maxWeight !== null 
            || $this->maxVolume !== null;
    }

    /**
     * Verifica si un tipo de item esta permitido
     */
    public function isItemTypeAllowed(string $itemType): bool
    {
        if ($this->allowedItemTypes === null || empty($this->allowedItemTypes)) {
            return true; // Sin restriccion = todo permitido
        }

        return in_array($itemType, $this->allowedItemTypes, true);
    }

    /**
     * Calcula cantidad disponible antes de alcanzar el maximo
     */
    public function getRemainingCapacity(int $currentQuantity): ?int
    {
        if ($this->maxQuantity === null) {
            return null; // Sin limite
        }

        return max(0, $this->maxQuantity - $currentQuantity);
    }

    /**
     * Verifica si una cantidad adicional cabe en la ubicacion
     */
    public function canAcceptQuantity(int $currentQuantity, int $additionalQuantity): bool
    {
        if ($this->maxQuantity === null) {
            return true;
        }

        return ($currentQuantity + $additionalQuantity) <= $this->maxQuantity;
    }

    // === Factory Methods ===

    public static function createDefault(string $id, string $locationId, ?string $workspaceId = null): self
    {
        return new self(
            id: $id,
            locationId: $locationId,
            workspaceId: $workspaceId,
        );
    }

    public static function createWithCapacity(
        string $id,
        string $locationId,
        int $maxQuantity,
        ?string $workspaceId = null
    ): self {
        return new self(
            id: $id,
            locationId: $locationId,
            maxQuantity: $maxQuantity,
            workspaceId: $workspaceId,
        );
    }

    // === Mutation Methods ===

    public function withMaxQuantity(?int $maxQuantity): self
    {
        return new self(
            $this->id,
            $this->locationId,
            $maxQuantity,
            $this->maxWeight,
            $this->maxVolume,
            $this->allowedItemTypes,
            $this->allowMixedLots,
            $this->allowMixedSkus,
            $this->fifoEnforced,
            $this->isActive,
            $this->workspaceId,
            $this->meta,
            $this->createdAt,
            new DateTimeImmutable(),
        );
    }

    public function withMaxWeight(?float $maxWeight): self
    {
        return new self(
            $this->id,
            $this->locationId,
            $this->maxQuantity,
            $maxWeight,
            $this->maxVolume,
            $this->allowedItemTypes,
            $this->allowMixedLots,
            $this->allowMixedSkus,
            $this->fifoEnforced,
            $this->isActive,
            $this->workspaceId,
            $this->meta,
            $this->createdAt,
            new DateTimeImmutable(),
        );
    }

    public function activate(): self
    {
        return new self(
            $this->id,
            $this->locationId,
            $this->maxQuantity,
            $this->maxWeight,
            $this->maxVolume,
            $this->allowedItemTypes,
            $this->allowMixedLots,
            $this->allowMixedSkus,
            $this->fifoEnforced,
            true,
            $this->workspaceId,
            $this->meta,
            $this->createdAt,
            new DateTimeImmutable(),
        );
    }

    public function deactivate(): self
    {
        return new self(
            $this->id,
            $this->locationId,
            $this->maxQuantity,
            $this->maxWeight,
            $this->maxVolume,
            $this->allowedItemTypes,
            $this->allowMixedLots,
            $this->allowMixedSkus,
            $this->fifoEnforced,
            false,
            $this->workspaceId,
            $this->meta,
            $this->createdAt,
            new DateTimeImmutable(),
        );
    }

    // === Serialization ===

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'location_id' => $this->locationId,
            'max_quantity' => $this->maxQuantity,
            'max_weight' => $this->maxWeight,
            'max_volume' => $this->maxVolume,
            'allowed_item_types' => $this->allowedItemTypes,
            'allow_mixed_lots' => $this->allowMixedLots,
            'allow_mixed_skus' => $this->allowMixedSkus,
            'fifo_enforced' => $this->fifoEnforced,
            'is_active' => $this->isActive,
            'workspace_id' => $this->workspaceId,
            'meta' => $this->meta,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
