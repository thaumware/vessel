<?php

namespace App\Stock\Domain\Contracts;

interface UnidadRepository
{
    public function findById(string $id): ?\App\Stock\Domain\Entities\Unidad;
    
}