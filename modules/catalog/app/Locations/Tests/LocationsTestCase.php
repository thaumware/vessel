<?php

namespace App\Locations\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base TestCase for Locations module unit tests.
 * Does NOT boot Laravel - for pure domain/application logic testing.
 */
abstract class LocationsTestCase extends TestCase
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

    protected function createLocationData(array $overrides = []): array
    {
        return array_merge([
            'id' => $this->generateUuid(),
            'name' => 'Test Location ' . mt_rand(1000, 9999),
            'addressId' => $this->generateUuid(),
            'type' => 'warehouse',
            'description' => 'A test location description',
        ], $overrides);
    }
}
