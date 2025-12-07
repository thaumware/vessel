<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Interfaces\LocationRepository;
use App\Shared\Domain\DTOs\FilterParams;
use App\Shared\Domain\DTOs\PaginatedResult;

class ListLocations
{
    public function __construct(private LocationRepository $repository)
    {
    }

    /**
     * Listar locations con filtros y paginación
     */
    public function execute(FilterParams $params): PaginatedResult
    {
        $filters = $params->filters;
        
        // Búsqueda por nombre
        if ($params->search) {
            $filters['search'] = $params->search;
        }
        
        $locations = empty($filters) 
            ? $this->repository->findAll()
            : $this->repository->findByFilters($filters);
        
        // Paginación en memoria (los repositorios deberían hacerlo en BD)
        $total = count($locations);
        $offset = $params->getOffset();
        $limit = $params->getLimit();
        $paginatedData = array_slice($locations, $offset, $limit);
        
        return new PaginatedResult(
            data: $paginatedData,
            total: $total,
            page: $params->page,
            perPage: $params->perPage,
            lastPage: (int) ceil($total / $params->perPage) ?: 1
        );
    }
}