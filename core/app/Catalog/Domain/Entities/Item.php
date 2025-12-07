<?php

namespace App\Catalog\Domain\Entities;

use App\Catalog\Domain\ValueObjects\ItemStatus;
use App\Shared\Domain\Traits\HasId;

/**
 * Item - Entidad del catálogo (concepto base y reutilizable)
 *
 * Mantiene el núcleo mínimo (name, status, opcionales como description/uom/notes)
 * y deja taxonomía/jerarquías fuera del agregado para evitar acoplar arrays de UUIDs.
 */
class Item
{
    use HasId;

    private ItemStatus $status;

    public function __construct(
        private string $id,
        private string $name,
        ItemStatus|string $status = ItemStatus::Active,
        private ?string $description = null,
        private ?string $workspaceId = null,
        private ?string $uomId = null,        // Unidad de medida por defecto
        private ?string $notes = null,
        private array $identifiers = [],
    ) {
        $this->setId($id);
        $this->status = $status instanceof ItemStatus
            ? $status
            : ItemStatus::from($status);
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

    public function getStatus(): ItemStatus
    {
        return $this->status;
    }

    public function getStatusValue(): string
    {
        return $this->status->value;
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
            'notes' => $this->notes,
            'status' => $this->status->value,
            'workspace_id' => $this->workspaceId,

        ];
    }
}