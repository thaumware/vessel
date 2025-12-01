<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\DeleteLocation;
use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;
use App\Locations\Tests\LocationsTestCase;

class DeleteLocationTest extends LocationsTestCase
{
    /** @var LocationRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private DeleteLocation $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LocationRepository::class);
        $this->useCase = new DeleteLocation($this->repository);
    }

    public function test_returns_true_when_location_deleted(): void
    {
        $id = $this->generateUuid();
        $location = new Location(
            id: $id,
            name: 'To Delete',
            type: LocationType::WAREHOUSE,
        );

        $this->repository
            ->method('findById')
            ->willReturn($location);

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($location);

        $result = $this->useCase->execute($id);

        $this->assertTrue($result);
    }

    public function test_returns_false_when_location_not_found(): void
    {
        $id = $this->generateUuid();

        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->repository
            ->expects($this->never())
            ->method('delete');

        $result = $this->useCase->execute($id);

        $this->assertFalse($result);
    }
}
