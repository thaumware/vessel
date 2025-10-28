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
        string $measure_id,
        ?string $description = null,
    ) {
        $this->setId($id);

        $this->name = $name;
        $this->description = $description;
    }

    // Otros métodos relevantes para la entidad Item

}