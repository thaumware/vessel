<?php

namespace App\Locations\Domain\Interfaces;

use App\Locations\Domain\Entities\Location;

interface LocationRepository
{

    public function findAll(): array;

    /**
     * Buscar locations con filtros
     * @param array $filters ['type' => string, 'parent_id' => string|null]
     */
    public function findByFilters(array $filters = []): array;

    public function findById(string $id): ?Location;

    public function save(Location $location): void;

    public function update(Location $location): void;

    public function delete(Location $location): void;


}