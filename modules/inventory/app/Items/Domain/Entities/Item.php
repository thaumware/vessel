<?php

namespace App\Items\Domain\Entities;

class Item
{
    private string $id;
    private string $name;
    private ?string $description;
    private float $price;
    private string $measure_id;

    public function __construct(
        string $id,
        string $name,
        string $measure_id,
        ?string $description = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->measure_id = $measure_id;
    }

    // Otros métodos relevantes para la entidad Item

}