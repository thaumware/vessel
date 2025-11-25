<?php

namespace App\Shared\Domain\Interfaces;

/**
 * IdGeneratorInterface - Contrato para generación de identificadores únicos.
 * 
 * Permite desacoplar el Domain de implementaciones específicas de UUID.
 */
interface IdGeneratorInterface
{
    /**
     * Genera un nuevo identificador único.
     */
    public function generate(): string;
}
