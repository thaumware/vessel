<?php

namespace App\Items\Domain\UseCases;

use App\Items\Domain\Entities\Item;
use App\Items\Domain\Interfaces\ItemRepositoryInterface;

class CreateItem
{
    public function __construct(
        private ItemRepositoryInterface $repository
    ) {}

    public function execute(
        string $id,
        string $name,
        ?string $description = null,
        ?string $uomId = null,
        ?string $notes = null,
        string $status = 'active',
        ?string $workspaceId = null,
        array $termIds = [],
    ): Item {
        $item = new Item(
            id: $id,
            name: $name,
            description: $description,
            uomId: $uomId,
            notes: $notes,
            status: $status,
            workspaceId: $workspaceId,
            termIds: $termIds,
        );

        $this->repository->save($item);

        return $item;
    }
}
