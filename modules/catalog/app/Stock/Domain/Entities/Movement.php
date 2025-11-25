<?php

namespace App\Stock\Domain\Entities;

use App\Shared\Domain\Traits\HasId;
use DateTimeImmutable;

class Movement
{
    use HasId;

    public function __construct(
        private string $id,
        private ?string $movementId,
        private string $sku,
        private ?string $locationFromId,
        private ?string $locationFromType,
        private ?string $locationToId,
        private ?string $locationToType,
        private int $quantity,
        private ?int $balanceAfter = null,
        private ?string $movementType = null,
        private ?string $reference = null,
        private ?string $userId = null,
        private ?string $workspaceId = null,
        private ?array $meta = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->setId($id);
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public function getMovementId(): ?string
    {
        return $this->movementId;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getLocationFromId(): ?string
    {
        return $this->locationFromId;
    }

    public function getLocationFromType(): ?string
    {
        return $this->locationFromType;
    }

    public function getLocationToId(): ?string
    {
        return $this->locationToId;
    }

    public function getLocationToType(): ?string
    {
        return $this->locationToType;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getBalanceAfter(): ?int
    {
        return $this->balanceAfter;
    }

    public function getMovementType(): ?string
    {
        return $this->movementType;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
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

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'movement_id' => $this->movementId,
            'sku' => $this->sku,
            'location_from_id' => $this->locationFromId,
            'location_from_type' => $this->locationFromType,
            'location_to_id' => $this->locationToId,
            'location_to_type' => $this->locationToType,
            'quantity' => $this->quantity,
            'balance_after' => $this->balanceAfter,
            'movement_type' => $this->movementType,
            'reference' => $this->reference,
            'user_id' => $this->userId,
            'workspace_id' => $this->workspaceId,
            'meta' => $this->meta,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
