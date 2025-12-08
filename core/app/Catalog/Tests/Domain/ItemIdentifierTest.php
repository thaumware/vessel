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
            item_id: $data['itemId'],
            type: $data['type'],
            value: $data['value'],
            is_primary: $data['isPrimary'],
            variant_id: $data['variantId'],
            label: $data['label'],
        );

        $this->assertEquals($data['id'], $identifier->getId());
        $this->assertEquals($data['itemId'], $identifier->itemId());
        $this->assertEquals($data['type'], $identifier->type()->value);
        $this->assertEquals($data['value'], $identifier->value());
        $this->assertTrue($identifier->isPrimary());
        $this->assertNull($identifier->variantId());
        $this->assertNull($identifier->label());
    }

    public function test_can_create_identifier_with_minimal_fields(): void
    {
        $id = $this->generateUuid();
        $itemId = $this->generateUuid();

        $identifier = new ItemIdentifier(
            id: $id,
            item_id: $itemId,
            type: 'sku',
            value: 'SKU-001'
        );

        $this->assertEquals($id, $identifier->getId());
        $this->assertEquals($itemId, $identifier->itemId());
        $this->assertEquals('sku', $identifier->type()->value);
        $this->assertEquals('SKU-001', $identifier->value());
        $this->assertFalse($identifier->isPrimary());
    }

    public function test_defaults_to_not_primary(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $this->generateUuid(),
            type: 'ean',
            value: '1234567890123'
        );

        $this->assertFalse($identifier->isPrimary());
    }

    public function test_can_create_ean_identifier(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $this->generateUuid(),
            type: 'ean',
            value: '4006381333931'
        );

        $this->assertEquals('ean', $identifier->type()->value);
    }

    public function test_can_create_upc_identifier(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $this->generateUuid(),
            type: 'upc',
            value: '012345678905'
        );

        $this->assertEquals('upc', $identifier->type()->value);
    }

    public function test_can_create_supplier_identifier(): void
    {
        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $this->generateUuid(),
            type: 'supplier',
            value: 'SUP-ABC-123',
            label: 'Supplier XYZ Code'
        );

        $this->assertEquals('supplier', $identifier->type()->value);
        $this->assertEquals('Supplier XYZ Code', $identifier->label());
    }

    public function test_can_create_variant_specific_identifier(): void
    {
        $variantId = $this->generateUuid();

        $identifier = new ItemIdentifier(
            id: $this->generateUuid(),
            item_id: $this->generateUuid(),
            type: 'sku',
            value: 'SKU-VARIANT-001',
            variant_id: $variantId
        );

        $this->assertEquals($variantId, $identifier->variantId());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $data = $this->createItemIdentifierData([
            'variantId' => $this->generateUuid(),
            'label' => 'Test Label',
        ]);

        $identifier = new ItemIdentifier(
            id: $data['id'],
            item_id: $data['itemId'],
            type: $data['type'],
            value: $data['value'],
            is_primary: $data['isPrimary'],
            variant_id: $data['variantId'],
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
            item_id: $this->generateUuid(),
            type: 'sku',
            value: 'TEST',
            is_primary: true,
            variant_id: $this->generateUuid()
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
