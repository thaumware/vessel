<?php

namespace App\Locations\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Location
{
    use HasId;
    private string $name;
    private ?string $description;
    private string $address_id;

    // warehouse, store, office, etc.
    private string $type;
    public function __construct(
        ?string $id,
        string $name,
        string $address_id,
        string $type,
        ?string $description = null
    ) {
        $this->setId($id, true);
        $this->name = $name;
        $this->address_id = $address_id;
        $this->type = $type;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getAddressId(): string
    {
        return $this->address_id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'address_id' => $this->address_id,
            'type' => $this->type,
            'description' => $this->description,
        ];
    }
}