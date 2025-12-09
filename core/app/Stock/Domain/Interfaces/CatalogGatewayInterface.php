<?php

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\StockItem;

/**
 * CatalogGatewayInterface - Contrato para conectar Stock con el Catálogo
 * 
 * Define cómo el dominio de Stock se comunica con el catálogo.
 * La implementación concreta (Portal, HTTP, etc.) está en Infrastructure.
 */
interface CatalogGatewayInterface
{
    /**
     * Vincular un StockItem con su item del catálogo
     */
    public function linkToCatalog(StockItem $stockItem): void;

    /**
     * Enriquecer StockItems con datos del catálogo
     * 
     * @param StockItem[]|iterable $stockItems
     * @return array StockItems con datos del catálogo adjuntos
     */
    public function attachCatalogData(iterable $stockItems): array;

    /**
     * Verificar si un item existe en el catálogo
     */
    public function catalogItemExists(string $catalogItemId, ?string $origin = null): bool;

    /**
     * Obtener información básica del item desde el catálogo.
     */
    public function getItem(string $itemId): ?array;

    /**
     * Buscar items en el catálogo por término de búsqueda.
     * Busca en título, SKU y descripción.
     * 
     * @param string $searchTerm Término de búsqueda
     * @param int $limit Máximo de resultados
     * @return array Array de items encontrados
     */
    public function searchItems(string $searchTerm, int $limit = 50): array;

    /**
     * Obtener el nombre del origen interno por defecto
     */
    public function getDefaultOriginName(): string;

    /**
     * Registrar un origen de catálogo
     */
    public function registerOrigin(string $name, string $source, string $type = 'table'): string;
}
