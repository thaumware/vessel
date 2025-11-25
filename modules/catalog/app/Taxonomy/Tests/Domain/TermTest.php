<?php

namespace App\Taxonomy\Tests\Domain;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Entities\Vocabulary;
use App\Taxonomy\Tests\TaxonomyTestCase;

class TermTest extends TaxonomyTestCase
{
    public function test_can_create_term(): void
    {
        $data = $this->createTermData();
        
        $term = new Term(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            vocabularyId: $data['vocabularyId'],
            description: $data['description'],
            workspaceId: $data['workspaceId'],
        );

        $this->assertEquals($data['id'], $term->getId());
        $this->assertEquals($data['name'], $term->getName());
        $this->assertEquals($data['slug'], $term->getSlug());
        $this->assertEquals($data['vocabularyId'], $term->getVocabularyId());
        $this->assertEquals($data['description'], $term->getDescription());
        $this->assertEquals($data['workspaceId'], $term->getWorkspaceId());
    }

    public function test_can_create_term_without_optional_fields(): void
    {
        $term = new Term(
            id: $this->generateUuid(),
            name: 'Simple Term',
            slug: 'simple-term',
            vocabularyId: $this->generateUuid(),
        );

        $this->assertNull($term->getDescription());
        $this->assertNull($term->getWorkspaceId());
    }

    public function test_can_update_name(): void
    {
        $term = new Term(
            id: $this->generateUuid(),
            name: 'Original Name',
            slug: 'original-name',
            vocabularyId: $this->generateUuid(),
        );

        $term->setName('Updated Name');

        $this->assertEquals('Updated Name', $term->getName());
    }

    public function test_to_array_uses_snake_case(): void
    {
        $term = new Term(
            id: $this->generateUuid(),
            name: 'Test Term',
            slug: 'test-term',
            vocabularyId: 'vocab-123',
            description: 'Test description',
            workspaceId: 'ws-456',
        );

        $array = $term->toArray();

        $this->assertArrayHasKey('vocabulary_id', $array);
        $this->assertArrayHasKey('workspace_id', $array);
        $this->assertEquals('vocab-123', $array['vocabulary_id']);
        $this->assertEquals('ws-456', $array['workspace_id']);
    }
}

class VocabularyTest extends TaxonomyTestCase
{
    public function test_can_create_vocabulary(): void
    {
        $data = $this->createVocabularyData();
        
        $vocabulary = new Vocabulary(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'],
            workspaceId: $data['workspaceId'],
        );

        $this->assertEquals($data['id'], $vocabulary->getId());
        $this->assertEquals($data['name'], $vocabulary->getName());
        $this->assertEquals($data['slug'], $vocabulary->getSlug());
        $this->assertEquals($data['description'], $vocabulary->getDescription());
        $this->assertEquals($data['workspaceId'], $vocabulary->getWorkspaceId());
    }

    public function test_can_create_vocabulary_without_optional_fields(): void
    {
        $vocabulary = new Vocabulary(
            id: $this->generateUuid(),
            name: 'Categories',
            slug: 'categories',
        );

        $this->assertNull($vocabulary->getDescription());
        $this->assertNull($vocabulary->getWorkspaceId());
    }

    public function test_can_update_name(): void
    {
        $vocabulary = new Vocabulary(
            id: $this->generateUuid(),
            name: 'Original Name',
            slug: 'original-name',
        );

        $vocabulary->setName('Updated Name');

        $this->assertEquals('Updated Name', $vocabulary->getName());
    }

    public function test_can_update_description(): void
    {
        $vocabulary = new Vocabulary(
            id: $this->generateUuid(),
            name: 'Test Vocab',
            slug: 'test-vocab',
        );

        $vocabulary->setDescription('New description');

        $this->assertEquals('New description', $vocabulary->getDescription());
    }

    public function test_to_array_uses_snake_case(): void
    {
        $vocabulary = new Vocabulary(
            id: $this->generateUuid(),
            name: 'Test Vocabulary',
            slug: 'test-vocabulary',
            description: 'Test description',
            workspaceId: 'ws-789',
        );

        $array = $vocabulary->toArray();

        $this->assertArrayHasKey('workspace_id', $array);
        $this->assertEquals('ws-789', $array['workspace_id']);
    }
}
