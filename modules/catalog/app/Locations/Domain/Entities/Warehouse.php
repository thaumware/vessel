<?php

namespace App\Locations\Domain\Entities;

class Warehouse extends Location
{
    public function __construct(
        string $id,
        string $name,
        string $address_id,
        ?string $description = null
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            address_id: $address_id,
            type: 'warehouse',
            description: $description
        );
    }
}