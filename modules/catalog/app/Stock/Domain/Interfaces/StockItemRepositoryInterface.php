<?php

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\StockItem;

interface StockItemRepositoryInterface
{
    /**
     * Obtener stock por ID
     */
    public function findById(string $id): ?StockItem;

    /**
     * Obtener stock por SKU
     */
    public function findBySku(string $sku): array;

    /**
     * Obtener stock por SKU y ubicación específica
     */
    public function findBySkuAndLocation(string $sku, string $locationId): ?StockItem;

    /**
     * Obtener todo el stock de una ubicación
     */
    public function findByLocation(string $locationId): array;

    /**
     * Obtener stock por item del catálogo
     */
    public function findByCatalogItemId(string $catalogItemId, string $catalogOrigin): array;

    /**
     * Buscar stock con filtros
     */
    public function search(array $filters = [], int $limit = 50, int $offset = 0): array;

    /**
     * Guardar nuevo stock item
     */
    public function save(StockItem $stockItem): StockItem;

    /**
     * Actualizar stock item existente
     */
    public function update(StockItem $stockItem): StockItem;

    /**
     * Eliminar stock item
     */
    public function delete(string $id): void;

    /**
     * Ajustar cantidad de stock (incrementar/decrementar)
     * Retorna el StockItem actualizado
     */
    public function adjustQuantity(string $sku, string $locationId, int $delta): StockItem;

    /**
     * Reservar cantidad de stock
     */
    public function reserve(string $id, int $quantity): StockItem;

    /**
     * Liberar cantidad reservada
     */
    public function release(string $id, int $quantity): StockItem;

    /**
     * Obtener stock con items del catálogo adjuntos (vía Portal)
     */
    public function findWithCatalogItems(array $ids): array;
}
