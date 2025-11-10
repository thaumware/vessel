<?php

namespace App\Items\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Item
{
    use HasId;
    private string $name;
    private ?string $description;


    public function __construct(
        string $id,
        string $name,
        ?string $description = null,
    ) {
        $this->setId($id);

        $this->name = $name;
        $this->description = $description;
    }

    // Otros métodos relevantes para la entidad Item

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }


}