<?php

namespace App\Shared\Domain\Traits;

/**
 * HasId - Trait para entidades con identificador único.
 * 
 * Solo gestiona el atributo ID, NO genera IDs.
 * La generación de IDs es responsabilidad de Infrastructure (repositorios).
 */
trait HasId
{
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
