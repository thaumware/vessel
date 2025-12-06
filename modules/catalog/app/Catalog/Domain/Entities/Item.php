<?php

namespace App\Catalog\Domain\Entities;

use App\Catalog\Domain\ValueObjects\ItemStatus;
use App\Shared\Domain\Traits\HasId;

/**
 * Item - Entidad del catálogo de productos (concepto base)
 * 
 * Diseño SaaS Enterprise:
 * - Solo 'name' es requerido para registro rápido
 * - Identificadores (SKU, EAN, etc.) van en ItemIdentifier (1:N)
 * - Variantes (colores, tallas) van en ItemVariant (1:N)  
 * - Taxonomía (marcas, categorías) es relación M:M via item_terms
 * - Stock se trackea por Variant, no por Item
 */
class Item
{
    use HasId;

    private ItemStatus $status;

    public function __construct(
        private string $id,
        private string $name,
        ItemStatus $status,
        private ?string $description = null,
        private ?string $workspaceId = null,
        private ?string $uomId = null,        // Unidad de medida por defecto
        private array $identifiers = [],
    ) {
        $this->setId($id);
        $this->status = $status;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUomId(): ?string
    {
        return $this->uomId;
    }

    public function getStatus(): ItemStatus
    {
        return $this->status;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspaceId;
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'description' => $this->description,
            'uom_id' => $this->uomId,
            'status' => $this->status->value,
            'workspace_id' => $this->workspaceId,

        ];
    }
}