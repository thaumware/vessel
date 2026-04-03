<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Domain\Interfaces\ItemIdentifierRepositoryInterface;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use InvalidArgumentException;

class CreateItemIdentifier
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private ItemIdentifierRepositoryInterface $identifierRepository
    ) {
    }

    public function execute(
        string $id,
        string $itemId,
        string $type,
        string $value,
        bool $isPrimary = false,
        ?string $variantId = null
    ): ItemIdentifier {
        $item = $this->itemRepository->findById($itemId);

        if (!$item) {
            throw new InvalidArgumentException('Item not found.');
        }

        $existingByValue = $this->identifierRepository->findByTypeAndValue($type, $value);

        if ($existingByValue && $existingByValue->itemId() !== $itemId) {
            throw new InvalidArgumentException('The identifier value is already assigned to another item.');
        }

        if ($existingByValue) {
            return $existingByValue;
        }

        $existingByItemType = $this->identifierRepository->findByItemAndType($itemId, $type, $variantId);

        if ($existingByItemType && $existingByItemType->value() !== $value) {
            throw new InvalidArgumentException('The item already has an identifier with this type.');
        }

        if ($existingByItemType) {
            return $existingByItemType;
        }

        $identifier = new ItemIdentifier(
            id: $id,
            item_id: $itemId,
            type: $type,
            value: $value,
            is_primary: $isPrimary,
            variant_id: $variantId
        );

        $this->identifierRepository->save($identifier);

        return $identifier;
    }
}
