<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Feature tests for Taxonomy module HTTP endpoints.
 */
class TaxonomyApiTest extends TestCase
{
    public function test_can_list_vocabularies(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/taxonomy/vocabularies');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                ]
            ]
        ]);
    }

    public function test_can_list_terms_by_vocabulary(): void
    {
        // First get a vocabulary
        $vocabResponse = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/taxonomy/vocabularies');

        if (empty($vocabResponse->json('data'))) {
            $this->markTestSkipped('No vocabularies available for testing');
        }

        $vocabularyId = $vocabResponse->json('data.0.id');

        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson("/api/taxonomy/vocabularies/{$vocabularyId}/terms");

        $response->assertStatus(200);
    }

    public function test_can_get_term_tree(): void
    {
        // First get a vocabulary
        $vocabResponse = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/taxonomy/vocabularies');

        if (empty($vocabResponse->json('data'))) {
            $this->markTestSkipped('No vocabularies available for testing');
        }

        $vocabularyId = $vocabResponse->json('data.0.id');

        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson("/api/taxonomy/vocabularies/{$vocabularyId}/tree");

        $response->assertStatus(200);
    }

    public function test_can_create_vocabulary(): void
    {
        $data = [
            'name' => 'Test Vocabulary ' . time(),
            'slug' => 'test-vocabulary-' . time(),
            'description' => 'A test vocabulary',
        ];

        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/taxonomy/vocabularies', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', $data['name']);
        $response->assertJsonPath('data.slug', $data['slug']);
    }

    public function test_can_create_term(): void
    {
        // First create or get a vocabulary
        $vocabResponse = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/taxonomy/vocabularies', [
                'name' => 'Vocab for Term ' . time(),
                'slug' => 'vocab-for-term-' . time(),
            ]);

        if ($vocabResponse->status() !== 201) {
            // Try to get existing
            $listResponse = $this->withAdapter('taxonomy', 'local')
                ->getJson('/api/taxonomy/vocabularies');
            
            if (empty($listResponse->json('data'))) {
                $this->markTestSkipped('No vocabularies available');
            }
            $vocabularyId = $listResponse->json('data.0.id');
        } else {
            $vocabularyId = $vocabResponse->json('data.id');
        }

        $termData = [
            'name' => 'Test Term ' . time(),
            'slug' => 'test-term-' . time(),
            'vocabulary_id' => $vocabularyId,
            'description' => 'A test term',
        ];

        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/taxonomy/terms', $termData);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', $termData['name']);
    }
}
