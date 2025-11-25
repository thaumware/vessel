<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;

class UpdateStockItem
{
    public function __construct(
        private StockItemRepositoryInterface $repository,
    ) {
    }

    /**
     * Actualizar un StockItem
     * @throws \RuntimeException si no existe el StockItem
     */
    public function execute(string $id, array $data): StockItem
    {
        $existing = $this->repository->findById($id);

        if (!$existing) {
            throw new \RuntimeException("StockItem not found: {$id}");
        }

        // Crear nueva instancia con datos actualizados (inmutabilidad)
        $updated = new StockItem(
            id: $existing->getId(),
            sku: $data['sku'] ?? $existing->getSku(),
            catalogItemId: $data['catalog_item_id'] ?? $existing->getCatalogItemId(),
            catalogOrigin: $data['catalog_origin'] ?? $existing->getCatalogOrigin(),
            locationId: $data['location_id'] ?? $existing->getLocationId(),
            locationType: $data['location_type'] ?? $existing->getLocationType(),
            quantity: $data['quantity'] ?? $existing->getQuantity(),
            reservedQuantity: $data['reserved_quantity'] ?? $existing->getReservedQuantity(),
            lotNumber: $data['lot_number'] ?? $existing->getLotNumber(),
            expirationDate: isset($data['expiration_date'])
                ? new \DateTimeImmutable($data['expiration_date'])
                : $existing->getExpirationDate(),
            serialNumber: $data['serial_number'] ?? $existing->getSerialNumber(),
            workspaceId: $data['workspace_id'] ?? $existing->getWorkspaceId(),
            meta: $data['meta'] ?? $existing->getMeta(),
            createdAt: $existing->getCreatedAt(),
        );

        return $this->repository->update($updated);
    }
}
