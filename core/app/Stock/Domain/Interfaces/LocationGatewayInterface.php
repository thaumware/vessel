<?php

declare(strict_types=1);

namespace App\Stock\Domain\Interfaces;

/**
 * Gateway para consultar informacion de ubicaciones desde el modulo Stock.
 * 
 * Stock necesita conocer la jerarquia de ubicaciones para calcular
 * capacidad total (ubicacion + hijos), pero no debe depender directamente
 * del modulo Locations.
 * 
 * La implementacion concreta puede:
 * - Llamar al API de Locations via HTTP
 * - Usar un adapter que consulta directamente (si estan en el mismo proceso)
 * - Usar cache para evitar llamadas repetidas
 */
interface LocationGatewayInterface
{
    /**
     * Obtener IDs de ubicaciones hijas (directas e indirectas)
     * 
     * @param string $locationId
     * @return array<string> IDs de todas las ubicaciones descendientes
     */
    public function getDescendantIds(string $locationId): array;

    /**
     * Obtener IDs de ubicaciones hijas directas (solo primer nivel)
     * 
     * @param string $locationId
     * @return array<string>
     */
    public function getChildrenIds(string $locationId): array;

    /**
     * Obtener ID de la ubicacion padre
     * 
     * @param string $locationId
     * @return string|null ID del padre o null si es raiz
     */
    public function getParentId(string $locationId): ?string;

    /**
     * Obtener la cadena de ancestros (de hijo a raiz)
     * 
     * @param string $locationId
     * @return array<string> IDs desde el padre inmediato hasta la raiz
     */
    public function getAncestorIds(string $locationId): array;

    /**
     * Verificar si una ubicacion existe
     */
    public function exists(string $locationId): bool;

    /**
     * Obtener tipo de ubicacion
     */
    public function getLocationType(string $locationId): ?string;
}
