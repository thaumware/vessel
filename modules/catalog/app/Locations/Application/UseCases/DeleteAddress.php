<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Interfaces\AddressRepository;

class DeleteAddress
{
    public function __construct(private AddressRepository $repository)
    {
    }

    public function execute(string $id): bool
    {
        $address = $this->repository->findById($id);

        if (!$address) {
            return false;
        }

        $this->repository->delete($address);
        return true;
    }
}
