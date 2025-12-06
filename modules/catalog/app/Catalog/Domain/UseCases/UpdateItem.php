<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;

class UpdateItem
{
    public function __construct(
        private ItemRepositoryInterface $repository
    ) {}

    public function execute(
        string $id,
        ?string $name = null,
        ?string $description = null,
        ?string $uomId = null,
        ?string $notes = null,
        ?string $status = null,
        ?array $termIds = null,
    ): ?Item {
        $existing = $this->repository->findById($id);

        if (!$existing) {
            return null;
        }

        $updated = new Item(
            id: $id,
            name: $name ?? $existing->getName(),
            description: $description ?? $existing->getDescription(),
            uomId: $uomId ?? $existing->getUomId(),
            notes: $notes ?? $existing->getNotes(),
            status: $status ?? $existing->getStatus(),
            workspaceId: $existing->getWorkspaceId(),
            termIds: $termIds ?? $existing->getTermIds(),
        );

        $this->repository->update($updated);

        return $updated;
    }
}
