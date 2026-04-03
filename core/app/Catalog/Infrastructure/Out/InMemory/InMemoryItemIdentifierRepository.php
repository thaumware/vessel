<?php

namespace App\Catalog\Infrastructure\Out\InMemory;

use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Domain\Interfaces\ItemIdentifierRepositoryInterface;

class InMemoryItemIdentifierRepository implements ItemIdentifierRepositoryInterface
{
    /** @var array<string, ItemIdentifier> */
    private array $identifiers = [];

    public function save(ItemIdentifier $identifier): void
    {
        $this->identifiers[$identifier->getId()] = $identifier;
    }

    public function findById(string $id): ?ItemIdentifier
    {
        return $this->identifiers[$id] ?? null;
    }

    public function findByTypeAndValue(string $type, string $value): ?ItemIdentifier
    {
        foreach ($this->identifiers as $identifier) {
            if ($identifier->type()->value === $type && $identifier->value() === $value) {
                return $identifier;
            }
        }

        return null;
    }

    public function findByItemAndType(string $itemId, string $type, ?string $variantId = null): ?ItemIdentifier
    {
        foreach ($this->identifiers as $identifier) {
            if (
                $identifier->itemId() === $itemId
                && $identifier->type()->value === $type
                && $identifier->variantId() === $variantId
            ) {
                return $identifier;
            }
        }

        return null;
    }

    public function findByValueForTypes(string $value, array $types): array
    {
        return array_values(array_filter(
            $this->identifiers,
            fn (ItemIdentifier $identifier) => $identifier->value() === $value
                && in_array($identifier->type()->value, $types, true)
        ));
    }
}
