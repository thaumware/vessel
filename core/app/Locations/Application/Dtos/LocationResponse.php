<?php

namespace App\Locations\Application\Dtos;

/**
 * DTO para respuesta de locación
 */
final class LocationResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $address_id,
        public readonly string $type,
        public readonly ?string $description = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address_id' => $this->address_id,
            'type' => $this->type,
            'description' => $this->description,
        ];
    }

    public static function fromEntity($location): self
    {
        return new self(
            id: $location->getId(),
            name: $location->getName(),
            address_id: $location->getAddressId(),
            type: $location->getType(),
            description: $location->getDescription(),
        );
    }
}
