<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\GetLocation;
use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;
use App\Locations\Tests\LocationsTestCase;

class GetLocationTest extends LocationsTestCase
{
    /** @var LocationRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private GetLocation $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LocationRepository::class);
        $this->useCase = new GetLocation($this->repository);
    }

    public function test_returns_location_when_found(): void
    {
        $data = $this->createLocationData();
        $location = new Location(
            id: $data['id'],
            name: $data['name'],
            type: LocationType::WAREHOUSE,
        );

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($data['id'])
            ->willReturn($location);

        $result = $this->useCase->execute($data['id']);

        $this->assertInstanceOf(Location::class, $result);
        $this->assertEquals($data['id'], $result->getId());
    }

    public function test_returns_null_when_not_found(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn(null);

        $result = $this->useCase->execute($id);

        $this->assertNull($result);
    }
}
