<?php

namespace App\Catalog\Domain\Interfaces;

use App\Catalog\Domain\Entities\ItemIdentifier;

interface ItemIdentifierRepositoryInterface
{
    public function save(ItemIdentifier $identifier): void;

    public function findById(string $id): ?ItemIdentifier;

    public function findByTypeAndValue(string $type, string $value): ?ItemIdentifier;

    public function findByItemAndType(string $itemId, string $type, ?string $variantId = null): ?ItemIdentifier;

    /**
     * @param string[] $types
     * @return ItemIdentifier[]
     */
    public function findByValueForTypes(string $value, array $types): array;
}
