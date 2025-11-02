<?php

namespace App\Taxonomy\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Vocabulary
{
    use HasId;
    private string $name;

    public function __construct(string $id, string $name)
    {
        $this->setId($id);

        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
