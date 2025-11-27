<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;

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

        // Parse type if provided as string
        $type = $existingLocation->getType();
        if (isset($data['type']) && is_string($data['type'])) {
            $type = LocationType::tryFrom($data['type']) ?? $existingLocation->getType();
        }

        // Create updated location (immutable pattern)
        $updatedLocation = new Location(
            id: $existingLocation->getId(),
            name: $data['name'] ?? $existingLocation->getName(),
            addressId: $data['address_id'] ?? $existingLocation->getAddressId(),
            type: $type,
            description: $data['description'] ?? $existingLocation->getDescription(),
            parentId: $data['parent_id'] ?? $existingLocation->getParentId(),
        );

        $this->repository->update($updatedLocation);
        return $updatedLocation;
    }
}