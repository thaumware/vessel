<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\ValueObjects\AddressType;

class CreateAddress
{
    public function __construct(private AddressRepository $repository)
    {
    }

    public function execute(string $id, array $data): Address
    {
        $typeString = $data['address_type'] ?? 'street';
        $type = AddressType::tryFrom($typeString) ?? AddressType::STREET;

        $address = new Address(
            id: $id,
            name: $data['name'],
            addressType: $type,
            parentAddressId: $data['parent_address_id'] ?? null,
            description: $data['description'] ?? null,
        );

        $this->repository->save($address);
        return $address;
    }
}
