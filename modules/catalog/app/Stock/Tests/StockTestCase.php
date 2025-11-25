<?php

namespace App\Stock\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base TestCase for Stock module unit tests.
 * Does NOT boot Laravel - for pure domain/application logic testing.
 */
abstract class StockTestCase extends TestCase
{
    protected function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    protected function createStockItemData(array $overrides = []): array
    {
        return array_merge([
            'id' => $this->generateUuid(),
            'sku' => 'TEST-SKU-' . mt_rand(1000, 9999),
            'catalogItemId' => $this->generateUuid(),
            'catalogOrigin' => 'catalog_items',
            'locationId' => $this->generateUuid(),
            'locationType' => 'warehouse',
            'quantity' => 100,
            'reservedQuantity' => 0,
            'lotNumber' => 'LOT-' . date('Ymd'),
            'expirationDate' => null,
            'serialNumber' => null,
            'workspaceId' => $this->generateUuid(),
            'meta' => [],
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
        ], $overrides);
    }
}
