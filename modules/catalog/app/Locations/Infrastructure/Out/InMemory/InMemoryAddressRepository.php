<?php

namespace App\Locations\Infrastructure\Out\InMemory;

use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;

class InMemoryAddressRepository implements AddressRepository
{
    /** @var array<string, Address> */
    private array $addresses = [];

    /**
     * @return Address[]
     */
    public function findAll(): array
    {
        return array_values($this->addresses);
    }

    public function findById(string $id): ?Address
    {
        return $this->addresses[$id] ?? null;
    }

    public function findByIdWithChildren(string $id): ?array
    {
        $address = $this->findById($id);

        if (!$address) {
            return null;
        }

        $children = $this->findByParentId($id);

        return [
            'address' => $address,
            'children' => $children,
        ];
    }

    /**
     * @return Address[]
     */
    public function findByParentId(string $parentId): array
    {
        return array_values(array_filter(
            $this->addresses,
            fn(Address $address) => $address->getParentAddressId() === $parentId
        ));
    }

    public function save(Address $address): void
    {
        $this->addresses[$address->getId()] = $address;
    }

    public function update(Address $address): void
    {
        $this->addresses[$address->getId()] = $address;
    }

    public function delete(Address $address): void
    {
        unset($this->addresses[$address->getId()]);
    }

    public function clear(): void
    {
        $this->addresses = [];
    }
}
