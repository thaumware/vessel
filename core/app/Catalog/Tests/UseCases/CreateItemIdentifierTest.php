<?php

namespace App\Catalog\Tests\UseCases;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Domain\Interfaces\ItemIdentifierRepositoryInterface;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Catalog\Domain\UseCases\CreateItemIdentifier;
use App\Catalog\Tests\CatalogTestCase;
use InvalidArgumentException;

class CreateItemIdentifierTest extends CatalogTestCase
{
    /** @var ItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $itemRepository;

    /** @var ItemIdentifierRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $identifierRepository;

    private CreateItemIdentifier $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->itemRepository = $this->createMock(ItemRepositoryInterface::class);
        $this->identifierRepository = $this->createMock(ItemIdentifierRepositoryInterface::class);
        $this->useCase = new CreateItemIdentifier($this->itemRepository, $this->identifierRepository);
    }

    public function test_can_create_a_new_barcode_identifier_for_an_item(): void
    {
        $itemData = $this->createItemData();
        $identifierData = $this->createItemIdentifierData([
            'itemId' => $itemData['id'],
            'type' => 'ean',
            'value' => '7791234567890',
        ]);

        $item = new Item(
            id: $itemData['id'],
            name: $itemData['name']
        );

        $this->itemRepository
            ->expects($this->once())
            ->method('findById')
            ->with($itemData['id'])
            ->willReturn($item);

        $this->identifierRepository
            ->expects($this->once())
            ->method('findByTypeAndValue')
            ->with('ean', '7791234567890')
            ->willReturn(null);

        $this->identifierRepository
            ->expects($this->once())
            ->method('findByItemAndType')
            ->with($itemData['id'], 'ean', null)
            ->willReturn(null);

        $this->identifierRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (ItemIdentifier $identifier) use ($identifierData) {
                return $identifier->getId() === $identifierData['id']
                    && $identifier->itemId() === $identifierData['itemId']
                    && $identifier->type()->value === $identifierData['type']
                    && $identifier->value() === $identifierData['value']
                    && $identifier->isPrimary() === $identifierData['isPrimary'];
            }));

        $result = $this->useCase->execute(
            id: $identifierData['id'],
            itemId: $identifierData['itemId'],
            type: $identifierData['type'],
            value: $identifierData['value'],
            isPrimary: $identifierData['isPrimary']
        );

        $this->assertInstanceOf(ItemIdentifier::class, $result);
        $this->assertEquals('7791234567890', $result->value());
        $this->assertEquals('ean', $result->type()->value);
    }

    public function test_rejects_when_the_barcode_already_belongs_to_another_item(): void
    {
        $firstItemId = $this->generateUuid();
        $secondItemId = $this->generateUuid();
        $existingIdentifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $firstItemId,
            type: 'upc',
            value: '123456789012',
            is_primary: true
        );

        $this->itemRepository
            ->method('findById')
            ->with($secondItemId)
            ->willReturn(new Item(
                id: $secondItemId,
                name: 'Producto distinto'
            ));

        $this->identifierRepository
            ->expects($this->once())
            ->method('findByTypeAndValue')
            ->with('upc', '123456789012')
            ->willReturn($existingIdentifier);

        $this->identifierRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The identifier value is already assigned to another item.');

        $this->useCase->execute(
            id: $this->generateUuid(),
            itemId: $secondItemId,
            type: 'upc',
            value: '123456789012',
            isPrimary: true
        );
    }
}
