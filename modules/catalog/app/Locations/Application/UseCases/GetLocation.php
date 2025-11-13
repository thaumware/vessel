<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Location;
use App\Locations\Domain\Interfaces\LocationRepository;

class GetLocation
{
    public function __construct(private LocationRepository $repository)
    {
    }

    public function execute(string $id): ?Location
    {
        return $this->repository->findById($id);
    }
}