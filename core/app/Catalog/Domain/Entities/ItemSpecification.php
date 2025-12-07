<?php

namespace App\Catalog\Domain\Entities;

use App\Catalog\Domain\ValueObjects\SpecDataType;
use App\Shared\Domain\Traits\HasId;

/**
 * ItemSpecification - Especificación key-value de un item
 * 
 * Ejemplos:
 * - Servicios: duration=2h, complexity=high, deliverables=3
 * - Productos: weight=500g, color=red, size=XL
 * - Animales: breed=Labrador, age=2, vaccinated=true
 */
final class ItemSpecification
{
    use HasId;

    private SpecDataType $data_type;

    public function __construct(
        private string $id,
        private string $item_id,
        private string $key,
        private string $value,
        SpecDataType|string $data_type = SpecDataType::String,
        private ?string $variant_id = null,
        private ?string $unit = null,
        private ?string $group = null,
        private int $sort_order = 0,
        private ?string $workspace_id = null,
    ) {
        $this->setId($id);
        $this->data_type = $data_type instanceof SpecDataType
            ? $data_type
            : SpecDataType::from($data_type);
    }

    public function itemId(): string
    {
        return $this->item_id;
    }

    public function variantId(): ?string
    {
        return $this->variant_id;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Valor casteado según el data_type
     */
    public function typedValue(): mixed
    {
        return $this->data_type->cast($this->value);
    }

    public function dataType(): SpecDataType
    {
        return $this->data_type;
    }

    public function unit(): ?string
    {
        return $this->unit;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function sortOrder(): int
    {
        return $this->sort_order;
    }

    public function workspaceId(): ?string
    {
        return $this->workspace_id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'item_id' => $this->item_id,
            'variant_id' => $this->variant_id,
            'key' => $this->key,
            'value' => $this->value,
            'typed_value' => $this->typedValue(),
            'data_type' => $this->data_type->value,
            'unit' => $this->unit,
            'group' => $this->group,
            'sort_order' => $this->sort_order,
        ];
    }
}
