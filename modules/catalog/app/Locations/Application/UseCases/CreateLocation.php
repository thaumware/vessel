<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;

class CreateLocation
{
    public function __construct(private LocationRepository $repository)
    {
    }

    public function execute(string $id, array $data): Location
    {
        // Business validation could go here
        // e.g., validate address_id exists, name is unique, etc.

        $typeString = $data['type'] ?? 'warehouse';
        $type = LocationType::tryFrom($typeString) ?? LocationType::WAREHOUSE;

        $location = new Location(
            id: $id,
            name: $data['name'],
            addressId: $data['address_id'],
            type: $type,
            description: $data['description'] ?? null,
            parentId: $data['parent_id'] ?? null,
        );

        $this->repository->save($location);
        return $location;
    }
}

