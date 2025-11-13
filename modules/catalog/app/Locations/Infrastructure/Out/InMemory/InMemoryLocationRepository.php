<?php

namespace App\Locations\Infrastructure\Out\InMemory;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;

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
                $location = new Location(
                    $locationData['id'],
                    $locationData['name'],
                    $locationData['address_id'],
                    $locationData['type'],
                    $locationData['description'] ?? null
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

    public function update(Location $location): void
    {
        $this->locations[$location->getId()] = $location;
    }

    public function delete(Location $location): void
    {
        unset($this->locations[$location->getId()]);
    }
}