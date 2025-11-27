<?php

namespace App\Locations\Domain\Entities;

use App\Locations\Domain\ValueObjects\LocationType;
use App\Shared\Domain\Traits\HasId;

/**
 * Location - Ubicación física en el sistema de inventario
 * 
 * Jerarquía:
 * - warehouse/store/etc pueden tener hijos (storage_unit)
 * - storage_unit no puede tener hijos
 * 
 * Ejemplo:
 *   Camptech (warehouse)
 *     └── Estante A (storage_unit, parent_id: camptech)
 *     └── Cajón 1 (storage_unit, parent_id: camptech)
 */
class Location
{
    use HasId;

    public function __construct(
        private string $id,
        private string $name,
        private string $addressId,
        private LocationType $type,
        private ?string $description = null,
        private ?string $parentId = null,  // Para jerarquía de ubicaciones
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

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'address_id' => $this->addressId,
            'type' => $this->type->value,
            'description' => $this->description,
            'parent_id' => $this->parentId,
        ];
    }
}