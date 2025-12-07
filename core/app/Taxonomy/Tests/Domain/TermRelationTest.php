<?php

namespace App\Taxonomy\Tests\Domain;

use App\Taxonomy\Domain\Entities\TermRelation;
use App\Taxonomy\Tests\TaxonomyTestCase;

class TermRelationTest extends TaxonomyTestCase
{
    public function test_can_create_term_relation(): void
    {
        $id = $this->generateUuid();
        $fromTermId = $this->generateUuid();
        $toTermId = $this->generateUuid();

        $relation = new TermRelation(
            id: $id,
            fromTermId: $fromTermId,
            toTermId: $toTermId,
            relationType: 'parent',
            depth: 1
        );

        $this->assertEquals($id, $relation->getId());
        $this->assertEquals($fromTermId, $relation->getFromTermId());
        $this->assertEquals($toTermId, $relation->getToTermId());
        $this->assertEquals('parent', $relation->getRelationType());
        $this->assertEquals(1, $relation->getDepth());
    }

    public function test_can_create_relation_with_default_values(): void
    {
        $relation = new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $this->generateUuid(),
            toTermId: $this->generateUuid()
        );

        $this->assertEquals('parent', $relation->getRelationType());
        $this->assertEquals(0, $relation->getDepth());
    }

    public function test_can_create_related_type_relation(): void
    {
        $relation = new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $this->generateUuid(),
            toTermId: $this->generateUuid(),
            relationType: 'related'
        );

        $this->assertEquals('related', $relation->getRelationType());
    }

    public function test_can_create_synonym_type_relation(): void
    {
        $relation = new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $this->generateUuid(),
            toTermId: $this->generateUuid(),
            relationType: 'synonym'
        );

        $this->assertEquals('synonym', $relation->getRelationType());
    }

    public function test_to_array_uses_snake_case(): void
    {
        $relation = new TermRelation(
            id: 'rel-123',
            fromTermId: 'term-1',
            toTermId: 'term-2',
            relationType: 'parent',
            depth: 2
        );

        $array = $relation->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('from_term_id', $array);
        $this->assertArrayHasKey('to_term_id', $array);
        $this->assertArrayHasKey('relation_type', $array);
        $this->assertArrayHasKey('depth', $array);
        $this->assertEquals('term-1', $array['from_term_id']);
        $this->assertEquals('term-2', $array['to_term_id']);
    }
}
