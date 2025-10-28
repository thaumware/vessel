<?php

namespace App\Items\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class ItemType
{
    use HasId;

    private string $name;

    public function __construct(
        string $id,
        string $name
    ) {
        $this->setId($id);
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}