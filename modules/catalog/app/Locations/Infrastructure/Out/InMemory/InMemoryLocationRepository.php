<?php

namespace App\Locations\Infrastructure\Out\InMemory;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\ValueObjects\LocationType;

class InMemoryLocationRepository implements LocationRepository
{
    private array $locations = [];

    public function __construct()
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $dataFile = __DIR__ . '/../Data/locations.php';
        
        if (file_exists($dataFile)) {
            $data = require $dataFile;
            
            foreach ($data as $locationData) {
                $type = LocationType::tryFrom($locationData['type']) ?? LocationType::WAREHOUSE;
                
                $location = new Location(
                    $locationData['id'],
                    $locationData['name'],
                    $type,
                    $locationData['address_id'] ?? null,
                    $locationData['description'] ?? null,
                    $locationData['parent_id'] ?? null,
                );
                $this->locations[$location->getId()] = $location;
            }
        }
    }

    public function save(Location $location): void
    {
        $this->locations[$location->getId()] = $location;
    }

    public function findById(string $id): ?Location
    {
        return $this->locations[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->locations);
    }

    public function findByFilters(array $filters = []): array
    {
        $results = $this->locations;
        
        if (isset($filters['type'])) {
            $results = array_filter($results, fn($loc) => $loc->getType()->value === $filters['type']);
        }
        
        if (isset($filters['parent_id'])) {
            $results = array_filter($results, fn($loc) => $loc->getParentId() === $filters['parent_id']);
        }
        
        if (isset($filters['root']) && $filters['root'] === true) {
            $results = array_filter($results, fn($loc) => $loc->getParentId() === null);
        }
        
        return array_values($results);
    }

    public function update(Location $location): void
    {
        $this->locations[$location->getId()] = $location;
    }

    public function delete(Location $location): void
    {
        unset($this->locations[$location->getId()]);
    }
}