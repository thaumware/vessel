<?php

namespace App\Locations\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Address
{
    use HasId;

    private string $name;
    private ?string $description;

    // street, city, state, zip, country, etc.
    private string $address_type;


    public function __construct(
        string $id,
        string $name,
        string $address_type,
        ?string $description = null
    ) {
        $this->setId($id);
        $this->name = $name;
        $this->address_type = $address_type;
        $this->description = $description;
    }

    public function isEqual(Address $other): bool
    {
        return $this->getId() === $other->getId()
            && $this->name === $other->name
            && $this->address_type === $other->address_type;
    }

}