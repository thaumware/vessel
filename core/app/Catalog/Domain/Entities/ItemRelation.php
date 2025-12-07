<?php

namespace App\Catalog\Domain\Entities;

use App\Catalog\Domain\ValueObjects\RelationType;
use App\Shared\Domain\Traits\HasId;

/**
 * ItemRelation - Relación entre dos items del catálogo
 * 
 * Ejemplos:
 * - Repuestos: Filtro de aceite -> Component -> Motor V8
 * - Compatibilidad: Filtro ABC -> Compatible -> Motor Toyota 2.0
 * - Accesorios: Funda -> Accessory -> iPhone 15
 * - Kits: Tornillo -> Bundle -> Kit de instalación
 */
final class ItemRelation
{
    use HasId;

    private RelationType $relation_type;

    public function __construct(
        private string $id,
        private string $item_id,
        private string $related_item_id,
        RelationType|string $relation_type,
        private int $quantity = 1,
        private bool $is_required = false,
        private int $sort_order = 0,
        private ?array $meta = null,
        private ?string $workspace_id = null,
    ) {
        $this->setId($id);
        $this->relation_type = $relation_type instanceof RelationType
            ? $relation_type
            : RelationType::from($relation_type);
    }

    public function itemId(): string
    {
        return $this->item_id;
    }

    public function relatedItemId(): string
    {
        return $this->related_item_id;
    }

    public function relationType(): RelationType
    {
        return $this->relation_type;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function isRequired(): bool
    {
        return $this->is_required;
    }

    public function sortOrder(): int
    {
        return $this->sort_order;
    }

    public function meta(): ?array
    {
        return $this->meta;
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
            'related_item_id' => $this->related_item_id,
            'relation_type' => $this->relation_type->value,
            'relation_label' => $this->relation_type->label(),
            'quantity' => $this->quantity,
            'is_required' => $this->is_required,
            'sort_order' => $this->sort_order,
            'meta' => $this->meta,
        ];
    }
}
