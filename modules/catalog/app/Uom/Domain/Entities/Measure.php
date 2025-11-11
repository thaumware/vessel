<?php

namespace App\Uom\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Measure
{
    use HasId;

    private string $code;
    private string $name;
    private ?string $description = null;

    public function __construct(string $id, string $code, string $name, ?string $description = null)
    {
        $this->setId($id);
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
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

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
