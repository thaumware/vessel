<?php

namespace App\Catalog\Tests\Domain;

use App\Catalog\Domain\Entities\Item;
use App\Catalog\Tests\CatalogTestCase;

class ItemTest extends CatalogTestCase
{
    public function test_can_create_item_with_all_fields(): void
    {
        $data = $this->createItemData();

        $item = new Item(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
            uomId: $data['uomId'],
            notes: $data['notes'],
            status: $data['status'],
            workspaceId: $data['workspaceId'],
            termIds: $data['termIds'],
        );

        $this->assertEquals($data['id'], $item->getId());
        $this->assertEquals($data['name'], $item->getName());
        $this->assertEquals($data['description'], $item->getDescription());
        $this->assertEquals($data['uomId'], $item->getUomId());
        $this->assertEquals($data['notes'], $item->getNotes());
        $this->assertEquals($data['status'], $item->getStatus());
        $this->assertEquals($data['workspaceId'], $item->getWorkspaceId());
        $this->assertEquals($data['termIds'], $item->getTermIds());
    }

    public function test_can_create_item_with_minimal_fields(): void
    {
        $id = $this->generateUuid();
        $name = 'Minimal Item';

        $item = new Item(
            id: $id,
            name: $name
        );

        $this->assertEquals($id, $item->getId());
        $this->assertEquals($name, $item->getName());
        $this->assertNull($item->getDescription());
        $this->assertNull($item->getUomId());
        $this->assertNull($item->getNotes());
        $this->assertEquals('active', $item->getStatus());
        $this->assertNull($item->getWorkspaceId());
        $this->assertEquals([], $item->getTermIds());
    }

    public function test_item_defaults_to_active_status(): void
    {
        $item = new Item(
            id: $this->generateUuid(),
            name: 'Test Item'
        );

        $this->assertEquals('active', $item->getStatus());
    }

    public function test_item_defaults_to_empty_term_ids(): void
    {
        $item = new Item(
            id: $this->generateUuid(),
            name: 'Test Item'
        );

        $this->assertIsArray($item->getTermIds());
        $this->assertEmpty($item->getTermIds());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $data = $this->createItemData();

        $item = new Item(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'],
            uomId: $data['uomId'],
            notes: $data['notes'],
            status: $data['status'],
            workspaceId: $data['workspaceId'],
            termIds: $data['termIds'],
        );

        $array = $item->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('uom_id', $array);
        $this->assertArrayHasKey('notes', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('workspace_id', $array);
        $this->assertArrayHasKey('term_ids', $array);
    }

    public function test_to_array_uses_snake_case_keys(): void
    {
        $item = new Item(
            id: $this->generateUuid(),
            name: 'Test',
            uomId: $this->generateUuid(),
            workspaceId: $this->generateUuid(),
            termIds: [$this->generateUuid()],
        );

        $array = $item->toArray();

        $this->assertArrayHasKey('uom_id', $array);
        $this->assertArrayNotHasKey('uomId', $array);
        $this->assertArrayHasKey('workspace_id', $array);
        $this->assertArrayNotHasKey('workspaceId', $array);
        $this->assertArrayHasKey('term_ids', $array);
        $this->assertArrayNotHasKey('termIds', $array);
    }

    public function test_can_create_item_with_draft_status(): void
    {
        $item = new Item(
            id: $this->generateUuid(),
            name: 'Draft Item',
            status: 'draft'
        );

        $this->assertEquals('draft', $item->getStatus());
    }

    public function test_can_create_item_with_archived_status(): void
    {
        $item = new Item(
            id: $this->generateUuid(),
            name: 'Archived Item',
            status: 'archived'
        );

        $this->assertEquals('archived', $item->getStatus());
    }

    public function test_can_create_item_with_multiple_term_ids(): void
    {
        $termIds = [
            $this->generateUuid(),
            $this->generateUuid(),
            $this->generateUuid(),
        ];

        $item = new Item(
            id: $this->generateUuid(),
            name: 'Item with Terms',
            termIds: $termIds
        );

        $this->assertCount(3, $item->getTermIds());
        $this->assertEquals($termIds, $item->getTermIds());
    }
}
