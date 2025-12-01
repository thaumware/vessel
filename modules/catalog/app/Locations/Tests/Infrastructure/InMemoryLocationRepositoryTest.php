<?php

namespace App\Locations\Tests\Infrastructure;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\ValueObjects\LocationType;
use App\Locations\Infrastructure\Out\InMemory\InMemoryLocationRepository;
use App\Locations\Tests\LocationsTestCase;

class InMemoryLocationRepositoryTest extends LocationsTestCase
{
    private InMemoryLocationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        // Create repository without loading data file
        $this->repository = new class extends InMemoryLocationRepository {
            public function __construct()
            {
                // Skip parent constructor to avoid loading data file
            }
        };
    }

    public function test_save_and_find_by_id(): void
    {
        $location = new Location(
            id: $this->generateUuid(),
            name: 'Test Warehouse',
            type: LocationType::WAREHOUSE
        );

        $this->repository->save($location);
        $found = $this->repository->findById($location->getId());

        $this->assertNotNull($found);
        $this->assertEquals($location->getId(), $found->getId());
        $this->assertEquals($location->getName(), $found->getName());
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->repository->findById($this->generateUuid());
        $this->assertNull($found);
    }

    public function test_find_all_returns_all_locations(): void
    {
        $this->repository->save(new Location($this->generateUuid(), 'Warehouse 1', LocationType::WAREHOUSE));
        $this->repository->save(new Location($this->generateUuid(), 'Store 1', LocationType::STORE));
        $this->repository->save(new Location($this->generateUuid(), 'Office 1', LocationType::OFFICE));

        $all = $this->repository->findAll();

        $this->assertCount(3, $all);
    }

    public function test_find_by_filters_type(): void
    {
        $this->repository->save(new Location($this->generateUuid(), 'Warehouse 1', LocationType::WAREHOUSE));
        $this->repository->save(new Location($this->generateUuid(), 'Warehouse 2', LocationType::WAREHOUSE));
        $this->repository->save(new Location($this->generateUuid(), 'Store 1', LocationType::STORE));

        $warehouses = $this->repository->findByFilters(['type' => 'warehouse']);

        $this->assertCount(2, $warehouses);
        foreach ($warehouses as $location) {
            $this->assertEquals(LocationType::WAREHOUSE, $location->getType());
        }
    }

    public function test_find_by_filters_parent_id(): void
    {
        $parentId = $this->generateUuid();
        $this->repository->save(new Location($parentId, 'Main Warehouse', LocationType::WAREHOUSE));
        $this->repository->save(new Location($this->generateUuid(), 'Shelf A', LocationType::STORAGE_UNIT, null, null, $parentId));
        $this->repository->save(new Location($this->generateUuid(), 'Shelf B', LocationType::STORAGE_UNIT, null, null, $parentId));
        $this->repository->save(new Location($this->generateUuid(), 'Other Shelf', LocationType::STORAGE_UNIT, null, null, $this->generateUuid()));

        $children = $this->repository->findByFilters(['parent_id' => $parentId]);

        $this->assertCount(2, $children);
    }

    public function test_find_by_filters_root_locations(): void
    {
        $parentId = $this->generateUuid();
        $this->repository->save(new Location($parentId, 'Main Warehouse', LocationType::WAREHOUSE));
        $this->repository->save(new Location($this->generateUuid(), 'Store', LocationType::STORE));
        $this->repository->save(new Location($this->generateUuid(), 'Shelf A', LocationType::STORAGE_UNIT, null, null, $parentId));

        $roots = $this->repository->findByFilters(['root' => true]);

        $this->assertCount(2, $roots);
        foreach ($roots as $location) {
            $this->assertNull($location->getParentId());
        }
    }

    public function test_update_modifies_location(): void
    {
        $id = $this->generateUuid();
        $original = new Location($id, 'Original', LocationType::WAREHOUSE);
        $this->repository->save($original);

        $updated = new Location($id, 'Updated', LocationType::STORE);
        $this->repository->update($updated);

        $found = $this->repository->findById($id);
        $this->assertEquals('Updated', $found->getName());
        $this->assertEquals(LocationType::STORE, $found->getType());
    }

    public function test_delete_removes_location(): void
    {
        $location = new Location($this->generateUuid(), 'To Delete', LocationType::WAREHOUSE);
        $this->repository->save($location);

        $this->repository->delete($location);

        $this->assertNull($this->repository->findById($location->getId()));
    }

    public function test_save_preserves_all_fields(): void
    {
        $addressId = $this->generateUuid();
        $parentId = $this->generateUuid();

        $location = new Location(
            id: $this->generateUuid(),
            name: 'Full Location',
            type: LocationType::STORAGE_UNIT,
            addressId: $addressId,
            description: 'Description text',
            parentId: $parentId,
        );

        $this->repository->save($location);
        $found = $this->repository->findById($location->getId());

        $this->assertEquals('Full Location', $found->getName());
        $this->assertEquals(LocationType::STORAGE_UNIT, $found->getType());
        $this->assertEquals($addressId, $found->getAddressId());
        $this->assertEquals('Description text', $found->getDescription());
        $this->assertEquals($parentId, $found->getParentId());
    }
}
