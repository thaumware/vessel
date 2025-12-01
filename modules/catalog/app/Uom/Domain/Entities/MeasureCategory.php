<?php

namespace App\Uom\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class MeasureCategory
{
    use HasId;

    public function __construct(
        private string $id,
        private string $code,
        private string $name,
        private ?string $description = null,
        private ?string $icon = null,
        private int $sortOrder = 0,
    ) {
        $this->setId($id);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'sort_order' => $this->sortOrder,
        ];
    }
}
