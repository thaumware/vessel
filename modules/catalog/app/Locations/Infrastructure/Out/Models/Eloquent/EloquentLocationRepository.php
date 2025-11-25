<?php

namespace App\Locations\Infrastructure\Out\Models\Eloquent;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;

class EloquentLocationRepository implements LocationRepository
{
    public function save(Location $location): void
    {
        $locationModel = LocationModel::find($location->getId()) ?? new LocationModel();

        $locationModel->id = $location->getId();
        $locationModel->name = $location->getName();
        $locationModel->description = $location->getDescription();
        $locationModel->type = $location->getType();
        $locationModel->address_id = $location->getAddressId();

        $locationModel->save();
    }

    public function findById(string $id): ?Location
    {
        $locationModel = LocationModel::find($id);

        if (!$locationModel) {
            return null;
        }

        return new Location(
            id: $locationModel->id,
            name: $locationModel->name,
            addressId: $locationModel->address_id,
            type: $locationModel->type,
            description: $locationModel->description
        );
    }

    public function findAll(): array
    {
        $locationModels = LocationModel::all();
        $locations = [];

        foreach ($locationModels as $locationModel) {
            $locations[] = new Location(
                id: $locationModel->id,
                name: $locationModel->name,
                addressId: $locationModel->address_id,
                type: $locationModel->type,
                description: $locationModel->description
            );
        }

        return $locations;
    }

    public function update(Location $location): void
    {
        $this->save($location);
    }

    public function delete(Location $location): void
    {
        $locationModel = LocationModel::find($location->getId());

        if ($locationModel) {
            $locationModel->delete();
        }
    }
}