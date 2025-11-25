<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;

class CreateLocation
{
    public function __construct(private LocationRepository $repository)
    {
    }

    public function execute(string $id, array $data): Location
    {
        // Business validation could go here
        // e.g., validate address_id exists, name is unique, etc.

        $location = new Location(
            id: $id,
            name: $data['name'],
            addressId: $data['address_id'],
            type: $data['type'] ?? 'location',
            description: $data['description'] ?? null
        );

        $this->repository->save($location);
        return $location;
    }
}

