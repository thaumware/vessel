<?php

namespace App\Catalog\Tests\UseCases;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Domain\Interfaces\ItemIdentifierRepositoryInterface;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Catalog\Domain\UseCases\FindItemByBarcode;
use App\Catalog\Tests\CatalogTestCase;
use DomainException;

class FindItemByBarcodeTest extends CatalogTestCase
{
    /** @var ItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $itemRepository;

    /** @var ItemIdentifierRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $identifierRepository;

    private FindItemByBarcode $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->itemRepository = $this->createMock(ItemRepositoryInterface::class);
        $this->identifierRepository = $this->createMock(ItemIdentifierRepositoryInterface::class);
        $this->useCase = new FindItemByBarcode($this->itemRepository, $this->identifierRepository);
    }

    public function test_returns_the_item_when_a_barcode_matches(): void
    {
        $item = new Item(
            id: $this->generateUuid(),
            name: 'Leche Entera 1L',
            status: 'active'
        );
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $item->getId(),
            type: 'ean',
            value: '7791234567890',
            is_primary: true
        );

        $this->identifierRepository
            ->expects($this->once())
            ->method('findByValueForTypes')
            ->with('7791234567890', ['ean', 'upc'])
            ->willReturn([$identifier]);

        $this->itemRepository
            ->expects($this->once())
            ->method('findById')
            ->with($item->getId())
            ->willReturn($item);

        $result = $this->useCase->execute('7791234567890');

        $this->assertNotNull($result);
        $this->assertSame($item, $result['item']);
        $this->assertSame($identifier, $result['identifier']);
    }

    public function test_returns_null_when_the_barcode_is_not_found(): void
    {
        $this->identifierRepository
            ->expects($this->once())
            ->method('findByValueForTypes')
            ->with('0000000000000', ['ean', 'upc'])
            ->willReturn([]);

        $this->itemRepository
            ->expects($this->never())
            ->method('findById');

        $this->assertNull($this->useCase->execute('0000000000000'));
    }

    public function test_throws_when_the_barcode_matches_multiple_items(): void
    {
        $firstIdentifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $this->generateUuid(),
            type: 'ean',
            value: '7791234567890',
            is_primary: true
        );
        $secondIdentifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $this->generateUuid(),
            type: 'upc',
            value: '7791234567890',
            is_primary: true
        );

        $this->identifierRepository
            ->method('findByValueForTypes')
            ->willReturn([$firstIdentifier, $secondIdentifier]);

        $this->itemRepository
            ->expects($this->never())
            ->method('findById');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Barcode matches multiple catalog items.');

        $this->useCase->execute('7791234567890');
    }
}
