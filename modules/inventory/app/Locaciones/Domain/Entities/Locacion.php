<?php

namespace App\Locaciones\Domain\Entities;

class Locacion
{
    private string $id;
    private string $nombre;
    private string $descripcion;

    public function __construct(
        string $id,
        string $nombre,
        string $descripcion
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }
}