<?php

namespace App\Locations\Application\UseCases;

use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\ValueObjects\AddressType;

class UpdateAddress
{
    public function __construct(private AddressRepository $repository)
    {
    }

    public function execute(string $id, array $data): ?Address
    {
        $address = $this->repository->findById($id);

        if (!$address) {
            return null;
        }

        $name = $data['name'] ?? $address->getName();
        $description = array_key_exists('description', $data)
            ? $data['description']
            : $address->getDescription();
        $parentAddressId = array_key_exists('parent_address_id', $data)
            ? $data['parent_address_id']
            : $address->getParentAddressId();

        $currentType = AddressType::tryFrom($address->getAddressType());
        $addressType = $currentType;
        if (isset($data['address_type'])) {
            $addressType = AddressType::tryFrom($data['address_type']) ?? $currentType;
        }

        $updatedAddress = new Address(
            id: $address->getId(),
            name: $name,
            addressType: $addressType,
            parentAddressId: $parentAddressId,
            description: $description,
        );

        $this->repository->update($updatedAddress);
        return $updatedAddress;
    }
}
