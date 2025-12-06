<?php

namespace App\Catalog\Domain\Entities;

use App\Catalog\Domain\ValueObjects\IdentifierType;
use App\Shared\Domain\Traits\HasId;

/**
 * ItemIdentifier - Códigos/identificadores de un Item o Variant
 * 
 * Permite múltiples identificadores por producto:
 * - SKU interno
 * - EAN/UPC (código de barras)
 * - Código de proveedor
 * - Número de parte del fabricante
 * - Código aduanero (HS Code)
 * - Cualquier código custom
 */
class ItemIdentifier
{
    use HasId;

    private IdentifierType $type;

    public function __construct(
        private string $id,
        private string $item_id,
        IdentifierType|string $type,
        private string $value,
        private bool $is_primary = false,
        private ?string $variant_id = null,
        private ?string $label = null,
    ) {
        $this->setId($id);
        $this->type = $type instanceof IdentifierType
            ? $type
            : IdentifierType::from($type);
    }

    public function itemId(): string
    {
        return $this->item_id;
    }

    public function variantId(): ?string
    {
        return $this->variant_id;
    }

    public function type(): IdentifierType
    {
        return $this->type;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    public function label(): ?string
    {
        return $this->label;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'item_id' => $this->item_id,
            'variant_id' => $this->variant_id,
            'type' => $this->type->value,
            'value' => $this->value,
            'is_primary' => $this->is_primary,
            'label' => $this->label,
        ];
    }
}
