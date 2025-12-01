<?php

namespace App\Items\Tests\Domain;

use App\Items\Domain\Entities\ItemType;
use App\Items\Tests\ItemsTestCase;

class ItemTypeTest extends ItemsTestCase
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
