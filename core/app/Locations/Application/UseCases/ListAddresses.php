<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;

class ListAddresses
{
    public function __construct(private AddressRepository $repository)
    {
    }

    /**
     * @return Address[]
     */
    public function execute(?string $parentId = null): array
    {
        if ($parentId !== null) {
            return $this->repository->findByParentId($parentId);
        }

        return $this->repository->findAll();
    }
}
