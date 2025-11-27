<?php

namespace App\Locations\Infrastructure\Out\Models\Eloquent;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;

class EloquentLocationRepository implements LocationRepository
{
    public function save(Location $location): void
    {
        $locationModel = LocationModel::find($location->getId()) ?? new LocationModel();

        $locationModel->id = $location->getId();
        $locationModel->name = $location->getName();
        $locationModel->description = $location->getDescription();
        $locationModel->type = $location->getType()->value;
        $locationModel->address_id = $location->getAddressId();
        $locationModel->parent_id = $location->getParentId();

        $locationModel->save();
    }

    public function findById(string $id): ?Location
    {
        $locationModel = LocationModel::find($id);

        if (!$locationModel) {
            return null;
        }

        return $this->toDomain($locationModel);
    }

    public function findAll(): array
    {
        return LocationModel::all()->map(fn($m) => $this->toDomain($m))->toArray();
    }

    public function findByFilters(array $filters = []): array
    {
        $query = LocationModel::query();
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }
        
        if (isset($filters['root']) && $filters['root'] === true) {
            $query->whereNull('parent_id');
        }
        
        return $query->get()->map(fn($m) => $this->toDomain($m))->toArray();
    }

    private function toDomain(LocationModel $model): Location
    {
        return new Location(
            id: $model->id,
            name: $model->name,
            addressId: $model->address_id,
            type: LocationType::tryFrom($model->type) ?? LocationType::WAREHOUSE,
            description: $model->description,
            parentId: $model->parent_id,
        );
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