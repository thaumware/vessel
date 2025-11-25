<?php

namespace App\Taxonomy\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base TestCase for Taxonomy module unit tests.
 * Does NOT boot Laravel - for pure domain/application logic testing.
 */
abstract class TaxonomyTestCase extends TestCase
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

    protected function createTermData(array $overrides = []): array
    {
        return array_merge([
            'id' => $this->generateUuid(),
            'name' => 'Test Term ' . mt_rand(1000, 9999),
            'slug' => 'test-term-' . mt_rand(1000, 9999),
            'vocabularyId' => $this->generateUuid(),
            'description' => 'A test term description',
            'workspaceId' => $this->generateUuid(),
        ], $overrides);
    }

    protected function createVocabularyData(array $overrides = []): array
    {
        return array_merge([
            'id' => $this->generateUuid(),
            'name' => 'Test Vocabulary ' . mt_rand(1000, 9999),
            'slug' => 'test-vocabulary-' . mt_rand(1000, 9999),
            'description' => 'A test vocabulary description',
            'workspaceId' => $this->generateUuid(),
        ], $overrides);
    }
}
