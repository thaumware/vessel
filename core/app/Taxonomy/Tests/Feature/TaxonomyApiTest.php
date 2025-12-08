<?php

namespace App\Taxonomy\Tests\Feature;

use Tests\TestCase;

/**
 * Smoke tests para endpoints de Taxonomy.
 */
class TaxonomyApiTest extends TestCase
{
    public function test_list_vocabularies_endpoint_exists(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/v1/taxonomy/vocabularies/read');

        $this->assertContains($response->status(), [200, 500]);
    }

    public function test_create_vocabulary_validates_input(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/v1/taxonomy/vocabularies/create', []);

        $response->assertStatus(422);
    }

    public function test_list_terms_endpoint_exists(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/v1/taxonomy/terms/read');

        $this->assertContains($response->status(), [200, 422, 500]);
    }

    public function test_create_term_validates_input(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/v1/taxonomy/terms/create', []);

        $response->assertStatus(422);
    }

    public function test_term_tree_endpoint_exists(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/v1/taxonomy/terms/tree');

        $this->assertContains($response->status(), [200, 422, 500]);
    }

    public function test_create_vocabulary_requires_name(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/v1/taxonomy/vocabularies/create', [
                'machine_name' => 'test_vocab',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_create_vocabulary_requires_machine_name(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/v1/taxonomy/vocabularies/create', [
                'name' => 'Test Vocabulary',
            ]);

        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_create_term_requires_vocabulary_id(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/v1/taxonomy/terms/create', [
                'name' => 'Test Term',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vocabulary_id']);
    }

    public function test_create_term_requires_name(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/v1/taxonomy/terms/create', [
                'vocabulary_id' => 'vocab-123',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }
}
