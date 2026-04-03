<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Interfaces\ItemIdentifierRepositoryInterface;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use DomainException;

class FindItemByBarcode
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private ItemIdentifierRepositoryInterface $identifierRepository
    ) {
    }

    public function execute(string $barcode): ?array
    {
        $matches = $this->identifierRepository->findByValueForTypes($barcode, ['ean', 'upc']);

        if (count($matches) === 0) {
            return null;
        }

        if (count($matches) > 1) {
            throw new DomainException('Barcode matches multiple catalog items.');
        }

        $identifier = $matches[0];
        $item = $this->itemRepository->findById($identifier->itemId());

        if (!$item) {
            throw new DomainException('Catalog item for barcode was not found.');
        }

        return [
            'item' => $item,
            'identifier' => $identifier,
        ];
    }
}
