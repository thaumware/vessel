<?php

namespace App\Uom\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base TestCase for Uom (Unit of Measure) module unit tests.
 * Does NOT boot Laravel - for pure domain/application logic testing.
 */
abstract class UomTestCase extends TestCase
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

    protected function createMeasureData(array $overrides = []): array
    {
        return array_merge([
            'id' => $this->generateUuid(),
            'code' => 'TST',
            'name' => 'Test Measure',
            'description' => 'A test measure description',
        ], $overrides);
    }

    protected function createConversionData(array $overrides = []): array
    {
        return array_merge([
            'fromMeasureId' => $this->generateUuid(),
            'toMeasureId' => $this->generateUuid(),
            'factor' => 1.0,
            'operation' => 'mul',
        ], $overrides);
    }
}
