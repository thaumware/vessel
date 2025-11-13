<?php

namespace App\Locations\Infrastructure\Out\Models;

use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\Entities\Location;

class ArrayLocationRepository implements LocationRepository
{
    private string $dataFile;

    public function __construct()
    {
        $this->dataFile = __DIR__ . '/../Data/locations.php';
    }

    /**
     * Read all locations from a local PHP array (temporary persistence).
     * This adapter can be swapped later for an SQL-backed repository without
     * changing the domain or use-cases.
     *
     * @return Location[]
     */
    public function findAll(): array
    {
        $data = require $this->dataFile;

        $locations = [];
        foreach ($data as $item) {
            $locations[] = new Location(
                $item['id'],
                $item['name'],
                $item['address_id'],
                $item['type'],
                $item['description'] ?? null
            );
        }

        return $locations;
    }

    public function findById(string $id): ?Location
    {
        $data = require $this->dataFile;

        foreach ($data as $item) {
            if ($item['id'] === $id) {
                return new Location(
                    $item['id'],
                    $item['name'],
                    $item['address_id'],
                    $item['type'],
                    $item['description'] ?? null
                );
            }
        }

        return null;
    }

    public function save(Location $location): void
    {
        $data = require $this->dataFile;

        // Check if location already exists
        $exists = false;
        foreach ($data as &$item) {
            if ($item['id'] === $location->getId()) {
                $item = [
                    'id' => $location->getId(),
                    'name' => $location->getName(),
                    'address_id' => $location->getAddressId(),
                    'type' => $location->getType(),
                    'description' => $location->getDescription()
                ];
                $exists = true;
                break;
            }
        }

        // If it doesn't exist, add it
        if (!$exists) {
            $data[] = [
                'id' => $location->getId(),
                'name' => $location->getName(),
                'address_id' => $location->getAddressId(),
                'type' => $location->getType(),
                'description' => $location->getDescription()
            ];
        }

        $this->writeData($data);
    }

    public function update(Location $location): void
    {
        $this->save($location); // For array storage, update is the same as save
    }

    public function delete(Location $location): void
    {
        $data = require $this->dataFile;

        $data = array_filter($data, fn($item) => $item['id'] !== $location->getId());

        $this->writeData($data);
    }

    private function writeData(array $data): void
    {
        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($this->dataFile, $content);
    }
}