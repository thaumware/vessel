<?php

namespace App\Stock\Infrastructure\Out\Gateways;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use Thaumware\Portal\Portal;

/**
 * PortalCatalogGateway - Implementación del CatalogGateway usando Portal
 * 
 * Conecta Stock con el Catálogo usando el paquete Portal para
 * relaciones cross-service.
 */
class PortalCatalogGateway implements CatalogGatewayInterface
{
    private const DEFAULT_ORIGIN = 'internal_catalog';

    public function __construct(
        private string $defaultOrigin = self::DEFAULT_ORIGIN,
    ) {
    }

    public function linkToCatalog(StockItem $stockItem): void
    {
        Portal::link(
            $stockItem->getId(),
            StockItem::class,
            $stockItem->getCatalogOrigin(),
            $stockItem->getCatalogItemId(),
            [
                'sku' => $stockItem->getSku(),
                'linked_at' => date('Y-m-d H:i:s'),
            ]
        );
    }

    public function attachCatalogData(iterable $stockItems): array
    {
        $items = is_array($stockItems) ? $stockItems : iterator_to_array($stockItems);
        
        if (empty($items)) {
            return [];
        }

        return Portal::attach($items);
    }

    public function catalogItemExists(string $catalogItemId, ?string $origin = null): bool
    {
        // Verificar existencia consultando Portal
        // Por ahora retornamos true - implementar según necesidad
        return true;
    }

    public function getDefaultOriginName(): string
    {
        return $this->defaultOrigin;
    }

    public function registerOrigin(string $name, string $source, string $type = 'table'): string
    {
        return Portal::register($name, $source, $type);
    }
}
