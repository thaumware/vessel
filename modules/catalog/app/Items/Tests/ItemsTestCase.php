<?php

namespace App\Items\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base TestCase for Items module unit tests.
 * Does NOT boot Laravel - for pure domain/application logic testing.
 */
abstract class ItemsTestCase extends TestCase
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

    protected function createItemData(array $overrides = []): array
    {
        return array_merge([
            'id' => $this->generateUuid(),
            'name' => 'Test Item ' . mt_rand(1000, 9999),
            'description' => 'A test item description',
            'uomId' => $this->generateUuid(),
            'notes' => 'Some notes',
            'status' => 'active',
            'workspaceId' => $this->generateUuid(),
            'termIds' => [$this->generateUuid(), $this->generateUuid()],
        ], $overrides);
    }

    protected function createItemIdentifierData(array $overrides = []): array
    {
        return array_merge([
            'id' => $this->generateUuid(),
            'itemId' => $this->generateUuid(),
            'type' => 'sku',
            'value' => 'SKU-' . mt_rand(10000, 99999),
            'isPrimary' => true,
            'variantId' => null,
            'label' => null,
        ], $overrides);
    }
}
