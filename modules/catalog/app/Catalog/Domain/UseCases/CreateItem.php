<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;

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
    ): Item {
        $item = new Item(
            id: $id,
            name: $name,
            description: $description,
            uomId: $uomId,
            notes: $notes,
            status: $status,
            workspaceId: $workspaceId,
        );

        $this->repository->save($item);

        return $item;
    }
}
