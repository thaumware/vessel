<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Interfaces\LocationRepository;

class ListLocations
{
    public function __construct(private LocationRepository $repository)
    {
    }

    /**
     * @param array $filters ['type' => string, 'parent_id' => string, 'root' => bool]
     */
    public function execute(array $filters = []): array
    {
        if (empty($filters)) {
            return $this->repository->findAll();
        }
        
        return $this->repository->findByFilters($filters);
    }
}