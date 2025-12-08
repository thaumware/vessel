<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ManageLocationSettings;

class ManageLocationSettingsInput
{
    public function __construct(
        public readonly string $locationId,
        public readonly ?int $maxQuantity = null,
        public readonly ?string $storageUomId = null,
        public readonly ?float $maxWeight = null,
        public readonly ?float $maxVolume = null,
        public readonly ?array $allowedItemTypes = null,
        public readonly bool $allowMixedLots = true,
        public readonly bool $allowMixedSkus = true,
        public readonly bool $fifoEnforced = false,
        public readonly bool $isActive = true,
        public readonly ?string $workspaceId = null,
        public readonly ?array $meta = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            locationId: $data['location_id'],
            maxQuantity: $data['max_quantity'] ?? null,
            storageUomId: $data['storage_uom_id'] ?? null,
            maxWeight: $data['max_weight'] ?? null,
            maxVolume: $data['max_volume'] ?? null,
            allowedItemTypes: $data['allowed_item_types'] ?? null,
            allowMixedLots: $data['allow_mixed_lots'] ?? true,
            allowMixedSkus: $data['allow_mixed_skus'] ?? true,
            fifoEnforced: $data['fifo_enforced'] ?? false,
            isActive: $data['is_active'] ?? true,
            workspaceId: $data['workspace_id'] ?? null,
            meta: $data['meta'] ?? null,
        );
    }
}
