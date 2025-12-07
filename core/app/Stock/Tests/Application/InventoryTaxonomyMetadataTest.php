<?php

namespace App\Stock\Tests\Application;

use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;
use RuntimeException;

/**
 * Demuestra cómo mapear taxonomías (categorias/marcas/tags) y metadata rica
 * sin usar SKU y con item_type explícito.
 * No persiste en BD: solo valida estructura y readiness para tablas term_assignments/metadata.
 */
class InventoryTaxonomyMetadataTest extends StockTestCase
{
    public function test_maps_taxonomy_and_metadata_from_fixture(): void
    {
        $fixturesDir = __DIR__ . '/../Support/data';
        $entries = $this->readJson($fixturesDir . '/inventory_seed_taxonomy.json');

        $terms = [];
        $metadataRows = [];
        foreach ($entries as $entry) {
            // Asegurar item_type explícito y sin sku
            $this->assertArrayHasKey('item_id', $entry);
            $this->assertArrayHasKey('item_type', $entry);
            $this->assertNotEmpty($entry['item_id']);
            $this->assertNotEmpty($entry['item_type']);

            // Normalizar taxonomía a term_assignments shape
            foreach ($entry['taxonomy'] as $vocab => $values) {
                foreach ($values as $term) {
                    $terms[] = [
                        'taxonomy' => $vocab,
                        'term' => $term,
                        'assignable_type' => 'movement',
                        'assignable_id' => $entry['item_id'] . '|' . $entry['location_id'],
                    ];
                }
            }

            // Descomponer metadata en filas key/value tipadas
            foreach ($entry['metadata'] as $key => $value) {
                $metadataRows[] = [
                    'parent_type' => 'movement',
                    'parent_id' => $entry['item_id'] . '|' . $entry['location_id'],
                    'key' => $key,
                    'data_type' => $this->inferType($value),
                    'value' => is_array($value) ? json_encode($value) : (string)$value,
                ];
            }
        }

        // Aserciones básicas de integridad
        $this->assertCount(4, array_filter($terms, fn($t) => $t['taxonomy'] === 'categorias'));
        $this->assertCount(2, array_filter($terms, fn($t) => $t['taxonomy'] === 'marcas'));
        $this->assertCount(4, array_filter($terms, fn($t) => $t['taxonomy'] === 'tags'));

        $this->assertTrue($this->hasMeta($metadataRows, 'supplier', 'ACME Bearings'));
        $this->assertTrue($this->hasMeta($metadataRows, 'supplier_certifications', json_encode(['iso9001','iso14001'])));
        $this->assertTrue($this->hasMeta($metadataRows, 'container', 'drum'));
    }

    private function hasMeta(array $rows, string $key, string $value): bool
    {
        foreach ($rows as $row) {
            if ($row['key'] === $key && $row['value'] === $value) {
                return true;
            }
        }
        return false;
    }

    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'bool',
            is_int($value) || is_float($value) => 'number',
            is_array($value) => 'json',
            $this->looksLikeDate($value) => 'date',
            default => 'string',
        };
    }

    private function looksLikeDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return (bool)strtotime($value);
    }

    private function readJson(string $path): array
    {
        $content = $this->readFixture($path);
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    private function readFixture(string $path): string
    {
        $data = @file_get_contents($path);
        if ($data === false) {
            throw new RuntimeException("Fixture not found: {$path}");
        }
        return $data;
    }
}
