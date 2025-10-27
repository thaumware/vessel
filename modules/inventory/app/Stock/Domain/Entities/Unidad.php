<?php

namespace App\Stock\Domain\Entities;

class Unidad
{
    private string $id;
    private string $identificador;
    private string $tipo_identificador;
    private string $detalle;

    public function __construct(
        string $id,
        string $identificador,
        string $tipo_identificador,
        string $detalle
    ) {
        $this->id = $id;
        $this->identificador = $identificador;
        $this->tipo_identificador = $tipo_identificador;
        $this->detalle = $detalle;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getIdentificador(): string
    {
        return $this->identificador;
    }

    public function getTipoIdentificador(): string
    {
        return $this->tipo_identificador;
    }

    public function getDetalle(): string
    {
        return $this->detalle;
    }
}