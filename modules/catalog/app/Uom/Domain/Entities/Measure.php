<?php

namespace App\Uom\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Measure
{
    use HasId;

    public function __construct(
        private string $id,
        private string $code,
        private string $name,
        private ?string $symbol = null,
        private ?string $category = null,
        private bool $isBase = false,
        private ?string $description = null,
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

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function isBase(): bool
    {
        return $this->isBase;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'category' => $this->category,
            'is_base' => $this->isBase,
            'description' => $this->description,
        ];
    }
}
