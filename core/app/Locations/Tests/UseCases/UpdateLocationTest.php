<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\UpdateLocation;
use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;
use App\Locations\Tests\LocationsTestCase;

class UpdateLocationTest extends LocationsTestCase
{
    /** @var LocationRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private UpdateLocation $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LocationRepository::class);
        $this->useCase = new UpdateLocation($this->repository);
    }

    public function test_updates_location_name(): void
    {
        $id = $this->generateUuid();
        $existingLocation = new Location(
            id: $id,
            name: 'Original Name',
            type: LocationType::WAREHOUSE,
        );

        $this->repository
            ->method('findById')
            ->willReturn($existingLocation);

        $this->repository
            ->expects($this->once())
            ->method('update');

        $result = $this->useCase->execute($id, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $result->getName());
    }

    public function test_updates_location_type(): void
    {
        $id = $this->generateUuid();
        $existingLocation = new Location(
            id: $id,
            name: 'Location',
            type: LocationType::WAREHOUSE,
        );

        $this->repository->method('findById')->willReturn($existingLocation);
        $this->repository->method('update');

        $result = $this->useCase->execute($id, ['type' => 'store']);

        $this->assertEquals(LocationType::STORE, $result->getType());
    }

    public function test_returns_null_when_location_not_found(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->repository
            ->expects($this->never())
            ->method('update');

        $result = $this->useCase->execute($id, ['name' => 'New Name']);

        $this->assertNull($result);
    }

    public function test_preserves_unchanged_fields(): void
    {
        $id = $this->generateUuid();
        $addressId = $this->generateUuid();
        $existingLocation = new Location(
            id: $id,
            name: 'Original',
            type: LocationType::WAREHOUSE,
            addressId: $addressId,
            description: 'Original description',
        );

        $this->repository->method('findById')->willReturn($existingLocation);
        $this->repository->method('update');

        $result = $this->useCase->execute($id, ['name' => 'Updated']);

        $this->assertEquals('Updated', $result->getName());
        $this->assertEquals($addressId, $result->getAddressId());
        $this->assertEquals('Original description', $result->getDescription());
        $this->assertEquals(LocationType::WAREHOUSE, $result->getType());
    }

    public function test_invalid_type_preserves_existing_type(): void
    {
        $id = $this->generateUuid();
        $existingLocation = new Location(
            id: $id,
            name: 'Location',
            type: LocationType::STORE,
        );

        $this->repository->method('findById')->willReturn($existingLocation);
        $this->repository->method('update');

        $result = $this->useCase->execute($id, ['type' => 'invalid_type']);

        $this->assertEquals(LocationType::STORE, $result->getType());
    }
}
