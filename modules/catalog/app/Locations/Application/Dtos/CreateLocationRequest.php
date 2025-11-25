<?php

namespace App\Locations\Application\Dtos;

/**
 * DTO para crear una locación
 */
final class CreateLocationRequest
{
    public function __construct(
        public readonly string $name,
        public readonly string $address_id,
        public readonly ?string $type = 'location',
        public readonly ?string $description = null,
        public readonly ?string $id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            address_id: $data['address_id'],
            type: $data['type'] ?? 'location',
            description: $data['description'] ?? null,
            id: $data['id'] ?? null,
        );
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
}
