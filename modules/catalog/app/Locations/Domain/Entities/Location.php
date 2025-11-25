<?php

namespace App\Locations\Domain\Entities;

use App\Locations\Domain\ValueObjects\LocationType;
use App\Shared\Domain\Traits\HasId;

class Location
{
    use HasId;

    public function __construct(
        private string $id,
        private string $name,
        private string $addressId,
        private LocationType $type,
        private ?string $description = null
    ) {
        $this->setId($id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddressId(): string
    {
        return $this->addressId;
    }

    public function getType(): LocationType
    {
        return $this->type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'address_id' => $this->addressId,
            'type' => $this->type->value,
            'description' => $this->description,
        ];
    }
}