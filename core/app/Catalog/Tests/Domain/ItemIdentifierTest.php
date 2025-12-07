<?php

namespace App\Catalog\Tests\Domain;

use App\Catalog\Domain\Entities\ItemIdentifier;
use App\Catalog\Tests\CatalogTestCase;

class ItemIdentifierTest extends CatalogTestCase
{
    public function test_can_create_identifier_with_all_fields(): void
    {
        $data = $this->createItemIdentifierData();

        $identifier = new ItemIdentifier(
            id: $data['id'],
            itemId: $data['itemId'],
            type: $data['type'],
            value: $data['value'],
            isPrimary: $data['isPrimary'],
            variantId: $data['variantId'],
            label: $data['label'],
        );

        $this->assertEquals($data['id'], $identifier->getId());
        $this->assertEquals($data['itemId'], $identifier->getItemId());
        $this->assertEquals($data['type'], $identifier->getType());
        $this->assertEquals($data['value'], $identifier->getValue());
        $this->assertTrue($identifier->isPrimary());
        $this->assertNull($identifier->getVariantId());
        $this->assertNull($identifier->getLabel());
    }

    public function test_can_create_identifier_with_minimal_fields(): void
    {
        $id = $this->generateUuid();
        $itemId = $this->generateUuid();

        $identifier = new ItemIdentifier(
            id: $id,
            itemId: $itemId,
            type: 'sku',
            value: 'SKU-001'
        );

        $this->assertEquals($id, $identifier->getId());
        $this->assertEquals($itemId, $identifier->getItemId());
        $this->assertEquals('sku', $identifier->getType());
        $this->assertEquals('SKU-001', $identifier->getValue());
        $this->assertFalse($identifier->isPrimary());
    }

    public function test_defaults_to_not_primary(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            itemId: $this->generateUuid(),
            type: 'ean',
            value: '1234567890123'
        );

        $this->assertFalse($identifier->isPrimary());
    }

    public function test_can_create_ean_identifier(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            itemId: $this->generateUuid(),
            type: 'ean',
            value: '4006381333931'
        );

        $this->assertEquals('ean', $identifier->getType());
    }

    public function test_can_create_upc_identifier(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            itemId: $this->generateUuid(),
            type: 'upc',
            value: '012345678905'
        );

        $this->assertEquals('upc', $identifier->getType());
    }

    public function test_can_create_supplier_identifier(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            itemId: $this->generateUuid(),
            type: 'supplier',
            value: 'SUP-ABC-123',
            label: 'Supplier XYZ Code'
        );

        $this->assertEquals('supplier', $identifier->getType());
        $this->assertEquals('Supplier XYZ Code', $identifier->getLabel());
    }

    public function test_can_create_variant_specific_identifier(): void
    {
        $variantId = $this->generateUuid();

        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            itemId: $this->generateUuid(),
            type: 'sku',
            value: 'SKU-VARIANT-001',
            variantId: $variantId
        );

        $this->assertEquals($variantId, $identifier->getVariantId());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $data = $this->createItemIdentifierData([
            'variantId' => $this->generateUuid(),
            'label' => 'Test Label',
        ]);

        $identifier = new ItemIdentifier(
            id: $data['id'],
            itemId: $data['itemId'],
            type: $data['type'],
            value: $data['value'],
            isPrimary: $data['isPrimary'],
            variantId: $data['variantId'],
            label: $data['label'],
        );

        $array = $identifier->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('item_id', $array);
        $this->assertArrayHasKey('variant_id', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('is_primary', $array);
        $this->assertArrayHasKey('label', $array);
    }

    public function test_to_array_uses_snake_case_keys(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            itemId: $this->generateUuid(),
            type: 'sku',
            value: 'TEST',
            isPrimary: true,
            variantId: $this->generateUuid()
        );

        $array = $identifier->toArray();

        $this->assertArrayHasKey('item_id', $array);
        $this->assertArrayNotHasKey('itemId', $array);
        $this->assertArrayHasKey('variant_id', $array);
        $this->assertArrayNotHasKey('variantId', $array);
        $this->assertArrayHasKey('is_primary', $array);
        $this->assertArrayNotHasKey('isPrimary', $array);
    }
}
