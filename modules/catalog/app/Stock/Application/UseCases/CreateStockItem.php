<?php

namespace App\Stock\Application\UseCases;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;

class CreateStockItem
{
    public function __construct(
        private StockItemRepositoryInterface $repository,
        private CatalogGatewayInterface $catalogGateway,
    ) {
    }

    public function execute(array $data): StockItem
    {
        // ID es obligatorio - debe venir desde el Controller (Infrastructure)
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException('ID is required');
        }

        $stockItem = new StockItem(
            id: $data['id'],
            sku: $data['sku'],
            catalogItemId: $data['catalog_item_id'],
            catalogOrigin: $data['catalog_origin'] ?? $this->catalogGateway->getDefaultOriginName(),
            locationId: $data['location_id'],
            locationType: $data['location_type'] ?? null,
            quantity: $data['quantity'] ?? 0,
            reservedQuantity: $data['reserved_quantity'] ?? 0,
            lotNumber: $data['lot_number'] ?? null,
            expirationDate: isset($data['expiration_date']) 
                ? new \DateTimeImmutable($data['expiration_date']) 
                : null,
            serialNumber: $data['serial_number'] ?? null,
            workspaceId: $data['workspace_id'] ?? null,
            meta: $data['meta'] ?? null,
        );

        // Guardar en repositorio
        $saved = $this->repository->save($stockItem);

        // Vincular con catálogo vía gateway
        $this->catalogGateway->linkToCatalog($saved);

        return $saved;
    }
}
