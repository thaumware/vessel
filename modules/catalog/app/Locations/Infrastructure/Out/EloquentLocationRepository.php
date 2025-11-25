<?php

namespace App\Locations\Infrastructure\Out;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;
use App\Locations\Infrastructure\Out\Models\Eloquent\LocationModel;

class EloquentLocationRepository implements LocationRepository
{
    public function findAll(): array
    {
        return LocationModel::all()
            ->map(fn($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findById(string $id): ?Location
    {
        $model = LocationModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function save(Location $location): void
    {
        LocationModel::create([
            'id' => $location->getId(),
            'name' => $location->getName(),
            'address_id' => $location->getAddressId(),
            'type' => $location->getType()->value,
            'description' => $location->getDescription(),
        ]);
    }

    public function update(Location $location): void
    {
        LocationModel::findOrFail($location->getId())->update([
            'name' => $location->getName(),
            'address_id' => $location->getAddressId(),
            'type' => $location->getType()->value,
            'description' => $location->getDescription(),
        ]);
    }

    public function delete(Location $location): void
    {
        LocationModel::destroy($location->getId());
    }

    private function toDomain(LocationModel $model): Location
    {
        return new Location(
            id: $model->id,
            name: $model->name,
            addressId: $model->address_id,
            type: LocationType::tryFrom($model->type) ?? LocationType::WAREHOUSE,
            description: $model->description,
        );
    }
}
