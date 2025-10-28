<?php

namespace App\Stock\Domain\Entities;

class Unit
{
    private string $id;
    private string $unit_id;
    private string $unit_id_type;
    private string $details;

    // Relaciones
    private string $catalog_item_id;
    

    public function __construct(
        string $id,
        string $unit_id,
        string $unit_id_type,
        string $details
    ) {
        $this->id = $id;
        $this->unit_id = $unit_id;
        $this->unit_id_type = $unit_id_type;
        $this->details = $details;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUnitId(): string
    {
        return $this->unit_id;
    }

    public function getUnitIdType(): string
    {
        return $this->unit_id_type;
    }

    public function getDetails(): string
    {
        return $this->details;
    }
}