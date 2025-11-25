<?php

namespace App\Locations\Tests\Domain;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\ValueObjects\LocationType;
use App\Locations\Tests\LocationsTestCase;

class LocationTest extends LocationsTestCase
{
    public function test_can_create_location(): void
    {
        $data = $this->createLocationData();
        
        $location = new Location(
            id: $data['id'],
            name: $data['name'],
            addressId: $data['addressId'],
            type: LocationType::WAREHOUSE,
            description: $data['description'],
        );

        $this->assertEquals($data['id'], $location->getId());
        $this->assertEquals($data['name'], $location->getName());
        $this->assertEquals($data['addressId'], $location->getAddressId());
        $this->assertEquals(LocationType::WAREHOUSE, $location->getType());
        $this->assertEquals($data['description'], $location->getDescription());
    }

    public function test_can_create_location_without_optional_fields(): void
    {
        $location = new Location(
            id: $this->generateUuid(),
            name: 'Simple Location',
            addressId: $this->generateUuid(),
            type: LocationType::STORE,
        );

        $this->assertNull($location->getDescription());
    }

    public function test_to_array_uses_snake_case(): void
    {
        $location = new Location(
            id: $this->generateUuid(),
            name: 'Test Location',
            addressId: 'addr-123',
            type: LocationType::WAREHOUSE,
            description: 'Test description',
        );

        $array = $location->toArray();

        $this->assertArrayHasKey('address_id', $array);
        $this->assertEquals('addr-123', $array['address_id']);
        $this->assertEquals('warehouse', $array['type']);
    }
}

class LocationTypeTest extends LocationsTestCase
{
    public function test_warehouse_can_have_children(): void
    {
        $this->assertTrue(LocationType::WAREHOUSE->canHaveChildren());
    }

    public function test_store_can_have_children(): void
    {
        $this->assertTrue(LocationType::STORE->canHaveChildren());
    }

    public function test_distribution_center_can_have_children(): void
    {
        $this->assertTrue(LocationType::DISTRIBUTION_CENTER->canHaveChildren());
    }

    public function test_storage_unit_cannot_have_children(): void
    {
        $this->assertFalse(LocationType::STORAGE_UNIT->canHaveChildren());
    }

    public function test_all_location_types_have_string_values(): void
    {
        $this->assertEquals('warehouse', LocationType::WAREHOUSE->value);
        $this->assertEquals('store', LocationType::STORE->value);
        $this->assertEquals('distribution_center', LocationType::DISTRIBUTION_CENTER->value);
        $this->assertEquals('office', LocationType::OFFICE->value);
        $this->assertEquals('storage_unit', LocationType::STORAGE_UNIT->value);
    }
}
