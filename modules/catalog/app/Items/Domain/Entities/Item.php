<?php

namespace App\Items\Domain\Entities;

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

    public function __construct(
        private string $id,
        private string $name,
        private ?string $description = null,
        private ?string $uomId = null,        // Unidad de medida por defecto
        private ?string $notes = null,
        private string $status = 'active',    // active, draft, archived
        private ?string $workspaceId = null,
        private array $termIds = [],          // IDs de términos asociados (M:M)
    ) {
        $this->setId($id);
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspaceId;
    }

    public function getTermIds(): array
    {
        return $this->termIds;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'description' => $this->description,
            'uom_id' => $this->uomId,
            'notes' => $this->notes,
            'status' => $this->status,
            'workspace_id' => $this->workspaceId,
            'term_ids' => $this->termIds,
        ];
    }
}