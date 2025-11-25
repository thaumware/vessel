<?php

namespace App\Locations\Domain\Entities;

use App\Locations\Domain\ValueObjects\AddressType;
use App\Shared\Domain\Traits\HasId;

class Address
{
    use HasId;


    public function __construct(
        private string $id,
        private string $name,
        private AddressType $addressType,
        private ?string $parentAddressId = null,
        private ?string $description = null
    ) {
        $this->setId($id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddressType(): string
    {
        return $this->addressType->value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getParentAddressId(): ?string
    {
        return $this->parentAddressId;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'address_type' => $this->getAddressType(),
            'description' => $this->getDescription(),
        ];
    }

    // non data methods 

    public function canBeParented(): bool
    {
        return in_array($this->addressType, [AddressType::CITY, AddressType::STATE, AddressType::COUNTRY]);
    }
}