<?php

namespace App\Catalog\Domain\Interfaces;

/**
 * Gateway de Taxonomy para validar términos y obtener su path jerárquico.
 */
interface TaxonomyGatewayInterface
{
    /**
     * Devuelve un mapa termId => ancestryPath (string o null) para los termIds solicitados.
     * Debe lanzar DomainException si alguno no existe o pertenece a otro vocabulary.
     *
     * @param string $vocabularyId
     * @param string[] $termIds
     * @return array<string, string|null>
     */
    public function getTermPaths(string $vocabularyId, array $termIds): array;
}
