<?php

namespace App\Items\Domain\Entities;

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

    public function __construct(
        private string $id,
        private string $itemId,
        private string $type,          // sku, ean, upc, supplier, manufacturer, custom
        private string $value,         // El código en sí
        private bool $isPrimary = false,
        private ?string $variantId = null,  // Si es específico de una variante
        private ?string $label = null,      // Etiqueta descriptiva (ej: "Código Proveedor X")
    ) {
        $this->setId($id);
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getVariantId(): ?string
    {
        return $this->variantId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'item_id' => $this->itemId,
            'variant_id' => $this->variantId,
            'type' => $this->type,
            'value' => $this->value,
            'is_primary' => $this->isPrimary,
            'label' => $this->label,
        ];
    }
}
