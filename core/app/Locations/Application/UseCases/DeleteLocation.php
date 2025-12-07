<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Interfaces\LocationRepository;

class DeleteLocation
{
    private LocationRepository $repository;

    public function __construct(LocationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $id): bool
    {
        $location = $this->repository->findById($id);

        if (!$location) {
            return false;
        }

        $this->repository->delete($location);
        return true;
    }
}