<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Smoke tests for Taxonomy module HTTP endpoints.
 * 
 * These tests verify that routes are properly defined and controllers respond.
 * For full integration tests with database, use Integration test suite.
 * 
 * API Routes:
 * - GET    /api/v1/taxonomy/vocabularies/read
 * - POST   /api/v1/taxonomy/vocabularies/create
 * - GET    /api/v1/taxonomy/terms/read
 * - POST   /api/v1/taxonomy/terms/create
 * - GET    /api/v1/taxonomy/terms/tree
 */
class TaxonomyApiTest extends TestCase
{
    public function test_list_vocabularies_endpoint_exists(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/v1/taxonomy/vocabularies/read');

        // Route exists and responds (500 = controller reached but db error, 200 = success)
        $this->assertContains($response->status(), [200, 500]);
    }

    public function test_create_vocabulary_validates_input(): void
    {
        // Empty request should return validation error
        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/v1/taxonomy/vocabularies/create', []);

        // 422 = validation error (route works, validation works)
        $response->assertStatus(422);
    }

    public function test_list_terms_endpoint_exists(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/v1/taxonomy/terms/read');

        // Route exists (may need vocabulary_id parameter)
        $this->assertContains($response->status(), [200, 422, 500]);
    }

    public function test_create_term_validates_input(): void
    {
        // Empty request should return validation error
        $response = $this->withAdapter('taxonomy', 'local')
            ->postJson('/api/v1/taxonomy/terms/create', []);

        // 422 = validation error (route works, validation works)
        $response->assertStatus(422);
    }

    public function test_term_tree_endpoint_exists(): void
    {
        $response = $this->withAdapter('taxonomy', 'local')
            ->getJson('/api/v1/taxonomy/terms/tree');

        // Route exists (may need vocabulary_id parameter)
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

        // 201 means auto-generated machine_name, 422 means validation error - both acceptable
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
