<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Interfaces\ItemIdentifierRepositoryInterface;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use DomainException;

class FindItemByIdentifierValue
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private ItemIdentifierRepositoryInterface $identifierRepository
    ) {
    }

    /**
     * @param string[] $types
     */
    public function execute(string $value, array $types = ['sku', 'ean', 'upc', 'custom']): ?array
    {
        $matches = $this->identifierRepository->findByValueForTypes($value, $types);

        if (count($matches) === 0) {
            return null;
        }

        if (count($matches) > 1) {
            throw new DomainException('Identifier matches multiple catalog items.');
        }

        $identifier = $matches[0];
        $item = $this->itemRepository->findById($identifier->itemId());

        if (!$item) {
            throw new DomainException('Catalog item for identifier was not found.');
        }

        return [
            'item' => $item,
            'identifier' => $identifier,
        ];
    }
}
