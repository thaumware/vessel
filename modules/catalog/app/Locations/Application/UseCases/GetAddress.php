<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;

class GetAddress
{
    public function __construct(private AddressRepository $repository)
    {
    }

    public function execute(string $id, bool $withChildren = false): Address|array|null
    {
        if ($withChildren) {
            return $this->repository->findByIdWithChildren($id);
        }

        return $this->repository->findById($id);
    }
}
