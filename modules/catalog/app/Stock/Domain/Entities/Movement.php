<?php

namespace App\Stock\Domain\Entities;

final class Movement
{
    public function __construct(
        private string $id,
        private ?string $movement_id,
        private string $sku,
        private ?string $location_from_id,
        private ?string $location_from_type,

        private ?string $location_to_id,
        private ?string $location_to_type,

        private int $quantity,
        private ?int $balance_after = null,
        private ?string $movement_type = null,
        private ?string $reference = null,
        private ?string $user_id = null,
        private ?string $workspace_id = null,
        private ?array $meta = null,
        private ?\DateTimeImmutable $created_at = null,
        private ?\DateTimeImmutable $processed_at = null
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }
    public function movementId(): ?string
    {
        return $this->movement_id;
    }
    public function sku(): string
    {
        return $this->sku;
    }
    public function locationFromId(): ?string
    {
        return $this->location_from_id;
    }
    public function locationFromType(): ?string
    {
        return $this->location_from_type;
    }
    public function locationToId(): ?string
    {
        return $this->location_to_id;
    }
    public function locationToType(): ?string
    {
        return $this->location_to_type;
    }
    public function quantity(): int
    {
        return $this->quantity;
    }
    public function balanceAfter(): ?int
    {
        return $this->balance_after;
    }
    public function movementType(): ?string
    {
        return $this->movement_type;
    }
    public function reference(): ?string
    {
        return $this->reference;
    }
    public function userId(): ?string
    {
        return $this->user_id;
    }
    public function workspaceId(): ?string
    {
        return $this->workspace_id;
    }
    public function meta(): ?array
    {
        return $this->meta;
    }
    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }
    public function processedAt(): ?\DateTimeImmutable
    {
        return $this->processed_at;
    }
}
