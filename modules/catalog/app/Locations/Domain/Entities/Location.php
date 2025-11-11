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
        string $id,
        string $name,
        string $address_id,
        string $type,
        ?string $description = null
    ) {
        $this->setId($id);
        $this->name = $name;
        $this->address_id = $address_id;
        $this->type = $type;
        $this->description = $description;
    }
}