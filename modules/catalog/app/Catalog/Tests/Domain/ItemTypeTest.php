<?php

namespace App\Catalog\Tests\Domain;

use App\Catalog\Domain\Entities\ItemType;
use App\Catalog\Tests\CatalogTestCase;

class ItemTypeTest extends CatalogTestCase
{
    public function test_can_create_item_type(): void
    {
        $id = $this->generateUuid();
        $name = 'Product';

        $type = new ItemType($id, $name);

        $this->assertEquals($id, $type->getId());
        $this->assertEquals($name, $type->getName());
    }

    public function test_can_create_service_type(): void
    {
        $type = new ItemType($this->generateUuid(), 'Service');
        $this->assertEquals('Service', $type->getName());
    }

    public function test_can_create_material_type(): void
    {
        $type = new ItemType($this->generateUuid(), 'Material');
        $this->assertEquals('Material', $type->getName());
    }

    public function test_can_create_consumable_type(): void
    {
        $type = new ItemType($this->generateUuid(), 'Consumable');
        $this->assertEquals('Consumable', $type->getName());
    }
}
