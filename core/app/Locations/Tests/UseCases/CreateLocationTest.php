<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\CreateLocation;
use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;
use App\Locations\Tests\LocationsTestCase;

class CreateLocationTest extends LocationsTestCase
{
    /** @var LocationRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private CreateLocation $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LocationRepository::class);
        $this->useCase = new CreateLocation($this->repository);
    }

    public function test_can_create_location_with_all_fields(): void
    {
        $data = $this->createLocationData();

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Location $location) use ($data) {
                return $location->getId() === $data['id']
                    && $location->getName() === $data['name']
                    && $location->getType()->value === $data['type']
                    && $location->getAddressId() === $data['address_id']
                    && $location->getDescription() === $data['description'];
            }));

        $result = $this->useCase->execute($data['id'], $data);

        $this->assertInstanceOf(Location::class, $result);
        $this->assertEquals($data['id'], $result->getId());
        $this->assertEquals($data['name'], $result->getName());
    }

    public function test_can_create_location_with_minimal_fields(): void
    {
        $id = $this->generateUuid();
        $data = ['name' => 'Minimal Location'];

        $this->repository->expects($this->once())->method('save');

        $result = $this->useCase->execute($id, $data);

        $this->assertEquals($id, $result->getId());
        $this->assertEquals('Minimal Location', $result->getName());
        $this->assertEquals(LocationType::WAREHOUSE, $result->getType());
    }

    public function test_defaults_to_warehouse_type(): void
    {
        $data = ['name' => 'Default Type Location'];

        $this->repository->method('save');

        $result = $this->useCase->execute($this->generateUuid(), $data);

        $this->assertEquals(LocationType::WAREHOUSE, $result->getType());
    }

    public function test_can_create_store_location(): void
    {
        $data = [
            'name' => 'Store Location',
            'type' => 'store',
        ];

        $this->repository->method('save');

        $result = $this->useCase->execute($this->generateUuid(), $data);

        $this->assertEquals(LocationType::STORE, $result->getType());
    }

    public function test_can_create_storage_unit_with_parent(): void
    {
        $parentId = $this->generateUuid();
        $data = [
            'name' => 'Shelf A',
            'type' => 'storage_unit',
            'parent_id' => $parentId,
        ];

        $this->repository->method('save');

        $result = $this->useCase->execute($this->generateUuid(), $data);

        $this->assertEquals(LocationType::STORAGE_UNIT, $result->getType());
        $this->assertEquals($parentId, $result->getParentId());
    }

    public function test_invalid_type_defaults_to_warehouse(): void
    {
        $data = [
            'name' => 'Invalid Type Location',
            'type' => 'invalid_type',
        ];

        $this->repository->method('save');

        $result = $this->useCase->execute($this->generateUuid(), $data);

        $this->assertEquals(LocationType::WAREHOUSE, $result->getType());
    }
}
