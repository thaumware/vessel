<?php

namespace App\Measures\Domain\Entities;

// Entidad que representa una medida
// como: unidad, kilogramo, litro, etc.
// ejemplo:
//  id: "kg", name: "Kilogramo", description: "Unidad de masa", abbreviation: "kg", measure_type: "weight"
// measure_base: "g", conversion_factor: 1000
class Measure
{
    private string $id;
    private string $name;
    private string $description;
    private string $abbreviation;
    private string $measure_type;
    private string $measure_from;
    private string $measure_to;
    private float $conversion_factor;

    public function __construct(
        string $id,
        string $name,
        string $abbreviation,
        string $measure_type,
        string $measure_from,
        ?string $description = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->abbreviation = $abbreviation;
        $this->measure_type = $measure_type;
        $this->measure_from = $measure_from;
    }



    // Otros métodos relevantes para la entidad Measure

}