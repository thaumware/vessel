<?php

namespace App\Catalog\Tests\UseCases;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Domain\Interfaces\ItemIdentifierRepositoryInterface;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Catalog\Domain\UseCases\FindItemByIdentifierValue;
use DomainException;
use PHPUnit\Framework\TestCase;

class FindItemByIdentifierValueTest extends TestCase
{
    private ItemRepositoryInterface $itemRepository;
    private ItemIdentifierRepositoryInterface $identifierRepository;
    private FindItemByIdentifierValue $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->itemRepository = $this->createMock(ItemRepositoryInterface::class);
        $this->identifierRepository = $this->createMock(ItemIdentifierRepositoryInterface::class);
        $this->useCase = new FindItemByIdentifierValue($this->itemRepository, $this->identifierRepository);
    }

    public function test_returns_the_item_when_an_identifier_matches(): void
    {
        $item = new Item(
            id: 'item-123',
            name: 'Producto por SKU',
            status: 'active'
        );

        $identifier = new ItemIdentifier(
            id: 'identifier-123',
            itemId: 'item-123',
            type: 'sku',
            value: 'SKU-001',
            isPrimary: true
        );

        $this->identifierRepository
            ->expects($this->once())
            ->method('findByValueForTypes')
            ->with('SKU-001', ['sku', 'ean', 'upc', 'custom'])
            ->willReturn([$identifier]);

        $this->itemRepository
            ->expects($this->once())
            ->method('findById')
            ->with('item-123')
            ->willReturn($item);

        $result = $this->useCase->execute('SKU-001');

        $this->assertSame($item, $result['item']);
        $this->assertSame($identifier, $result['identifier']);
    }

    public function test_returns_null_when_the_identifier_is_not_found(): void
    {
        $this->identifierRepository
            ->expects($this->once())
            ->method('findByValueForTypes')
            ->with('SKU-404', ['sku', 'ean', 'upc', 'custom'])
            ->willReturn([]);

        $this->itemRepository
            ->expects($this->never())
            ->method('findById');

        $result = $this->useCase->execute('SKU-404');

        $this->assertNull($result);
    }

    public function test_throws_when_the_identifier_matches_multiple_items(): void
    {
        $firstIdentifier = new ItemIdentifier(
            id: 'identifier-123',
            itemId: 'item-123',
            type: 'sku',
            value: 'SHARED-CODE'
        );

        $secondIdentifier = new ItemIdentifier(
            id: 'identifier-456',
            itemId: 'item-456',
            type: 'custom',
            value: 'SHARED-CODE'
        );

        $this->identifierRepository
            ->expects($this->once())
            ->method('findByValueForTypes')
            ->with('SHARED-CODE', ['sku', 'ean', 'upc', 'custom'])
            ->willReturn([$firstIdentifier, $secondIdentifier]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Identifier matches multiple catalog items.');

        $this->useCase->execute('SHARED-CODE');
    }
}
