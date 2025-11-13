<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Interfaces\LocationRepository;

class ListLocations
{
    public function __construct(private LocationRepository $repository)
    {
    }

    public function execute(): array
    {
        return $this->repository->findAll();
    }
}