<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;

class CreateLocation
{
    public function __construct(private LocationRepository $repository)
    {
    }

    public function execute(array $data): Location
    {
        // Business validation could go here
        // e.g., validate address_id exists, name is unique, etc.

        $location = new Location(
            id: $data['id'] ?? null,
            name: $data['name'],
            address_id: $data['address_id'],
            type: $data['type'] ?? 'location',
            description: $data['description'] ?? null
        );

        $this->repository->save($location);
        return $location;
    }
}

