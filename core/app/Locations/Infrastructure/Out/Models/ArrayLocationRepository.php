<?php

namespace App\Locations\Infrastructure\Out\Models;

use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\ValueObjects\LocationType;

/**
 * ArrayLocationRepository - Repositorio basado en archivo PHP
 * 
 * @deprecated Usar InMemoryLocationRepository o EloquentLocationRepository
 */
class ArrayLocationRepository implements LocationRepository
{
    private string $dataFile;

    public function __construct()
    {
        $this->dataFile = __DIR__ . '/../Data/locations.php';
    }

    public function findAll(): array
    {
        $data = require $this->dataFile;
        return array_map(fn($item) => $this->toDomain($item), $data);
    }

    public function findByFilters(array $filters = []): array
    {
        $data = require $this->dataFile;
        $results = $data;

        if (isset($filters['type'])) {
            $results = array_filter($results, fn($item) => $item['type'] === $filters['type']);
        }

        if (isset($filters['parent_id'])) {
            $results = array_filter($results, fn($item) => ($item['parent_id'] ?? null) === $filters['parent_id']);
        }

        if (isset($filters['root']) && $filters['root'] === true) {
            $results = array_filter($results, fn($item) => empty($item['parent_id']));
        }

        return array_map(fn($item) => $this->toDomain($item), array_values($results));
    }

    public function findById(string $id): ?Location
    {
        $data = require $this->dataFile;

        foreach ($data as $item) {
            if ($item['id'] === $id) {
                return $this->toDomain($item);
            }
        }

        return null;
    }

    public function save(Location $location): void
    {
        $data = require $this->dataFile;

        $exists = false;
        foreach ($data as &$item) {
            if ($item['id'] === $location->getId()) {
                $item = $this->toArray($location);
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $data[] = $this->toArray($location);
        }

        $this->writeData($data);
    }

    public function update(Location $location): void
    {
        $this->save($location);
    }

    public function delete(Location $location): void
    {
        $data = require $this->dataFile;
        $data = array_filter($data, fn($item) => $item['id'] !== $location->getId());
        $this->writeData(array_values($data));
    }

    private function toDomain(array $item): Location
    {
        $type = $item['type'] instanceof LocationType 
            ? $item['type'] 
            : (LocationType::tryFrom($item['type']) ?? LocationType::WAREHOUSE);

        return new Location(
            id: $item['id'],
            name: $item['name'],
            type: $type,
            addressId: $item['address_id'] ?? null,
            description: $item['description'] ?? null,
            parentId: $item['parent_id'] ?? null,
        );
    }

    private function toArray(Location $location): array
    {
        return [
            'id' => $location->getId(),
            'name' => $location->getName(),
            'type' => $location->getType()->value,
            'address_id' => $location->getAddressId(),
            'description' => $location->getDescription(),
            'parent_id' => $location->getParentId(),
        ];
    }

    private function writeData(array $data): void
    {
        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($this->dataFile, $content);
    }
}