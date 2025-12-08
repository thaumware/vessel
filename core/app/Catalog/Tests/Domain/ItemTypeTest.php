<?php

namespace App\Catalog\Tests\Domain;

use App\Catalog\Domain\Entities\ItemType;
use App\Catalog\Tests\CatalogTestCase;

class ItemTypeTest extends CatalogTestCase
{
    public function test_can_create_item_type(): void
    {
        $this->markTestSkipped('ItemType entity not yet implemented');
        $id = $this->generateUuid();
        $name = 'Product';

        $type = new ItemType($id, $name);

        $this->assertEquals($id, $type->getId());
        $this->assertEquals($name, $type->getName());
    }

    public function test_can_create_service_type(): void
    {
        $this->markTestSkipped('ItemType entity not yet implemented');
    }

    public function test_can_create_material_type(): void
    {
        $this->markTestSkipped('ItemType entity not yet implemented');
    }

    public function test_can_create_consumable_type(): void
    {
        $this->markTestSkipped('ItemType entity not yet implemented');
    }
}
