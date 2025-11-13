<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;

class UpdateLocation
{
    public function __construct(private LocationRepository $repository)
    {
    }

    public function execute(string $id, array $data): ?Location
    {
        $existingLocation = $this->repository->findById($id);
        if (!$existingLocation) {
            return null;
        }

        // Create updated location (immutable pattern)
        $updatedLocation = new Location(
            id: $existingLocation->getId(),
            name: $data['name'] ?? $existingLocation->getName(),
            address_id: $data['address_id'] ?? $existingLocation->getAddressId(),
            type: $data['type'] ?? $existingLocation->getType(),
            description: $data['description'] ?? $existingLocation->getDescription()
        );

        $this->repository->update($updatedLocation);
        return $updatedLocation;
    }
}