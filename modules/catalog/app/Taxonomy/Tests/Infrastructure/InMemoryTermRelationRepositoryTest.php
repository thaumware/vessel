<?php

namespace App\Taxonomy\Tests\Infrastructure;

use App\Taxonomy\Domain\Entities\TermRelation;
use App\Taxonomy\Infrastructure\Out\InMemory\InMemoryTermRelationRepository;
use App\Taxonomy\Tests\TaxonomyTestCase;

class InMemoryTermRelationRepositoryTest extends TaxonomyTestCase
{
    private InMemoryTermRelationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryTermRelationRepository();
    }

    public function test_can_save_and_find_relation(): void
    {
        $relation = $this->createRelation();
        
        $this->repository->save($relation);
        
        $found = $this->repository->findById($relation->getId());
        
        $this->assertNotNull($found);
        $this->assertEquals($relation->getId(), $found->getId());
        $this->assertEquals($relation->getFromTermId(), $found->getFromTermId());
        $this->assertEquals($relation->getToTermId(), $found->getToTermId());
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->repository->findById($this->generateUuid());
        
        $this->assertNull($found);
    }

    public function test_can_check_if_relation_exists(): void
    {
        $relation = $this->createRelation();
        $this->repository->save($relation);

        $exists = $this->repository->existsRelation(
            $relation->getFromTermId(),
            $relation->getToTermId(),
            $relation->getRelationType()
        );

        $this->assertTrue($exists);
    }

    public function test_exists_relation_returns_false_for_nonexistent(): void
    {
        $exists = $this->repository->existsRelation(
            $this->generateUuid(),
            $this->generateUuid(),
            'parent'
        );

        $this->assertFalse($exists);
    }

    public function test_can_delete_relation(): void
    {
        $relation = $this->createRelation();
        $this->repository->save($relation);

        $this->repository->delete($relation);

        $found = $this->repository->findById($relation->getId());
        $this->assertNull($found);
    }

    public function test_can_delete_by_terms(): void
    {
        $relation = $this->createRelation();
        $this->repository->save($relation);

        $this->repository->deleteByTerms(
            $relation->getFromTermId(),
            $relation->getToTermId(),
            $relation->getRelationType()
        );

        $exists = $this->repository->existsRelation(
            $relation->getFromTermId(),
            $relation->getToTermId(),
            $relation->getRelationType()
        );

        $this->assertFalse($exists);
    }

    public function test_can_get_parent_id(): void
    {
        $childTermId = $this->generateUuid();
        $parentTermId = $this->generateUuid();

        $relation = new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $childTermId,
            toTermId: $parentTermId,
            relationType: 'parent'
        );

        $this->repository->save($relation);

        $foundParentId = $this->repository->getParentId($childTermId);

        $this->assertEquals($parentTermId, $foundParentId);
    }

    public function test_get_parent_id_returns_null_for_root(): void
    {
        $parentId = $this->repository->getParentId($this->generateUuid());

        $this->assertNull($parentId);
    }

    public function test_can_get_children_ids(): void
    {
        $parentTermId = $this->generateUuid();
        $child1Id = $this->generateUuid();
        $child2Id = $this->generateUuid();

        // child1 -> parent
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $child1Id,
            toTermId: $parentTermId,
            relationType: 'parent'
        ));

        // child2 -> parent
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $child2Id,
            toTermId: $parentTermId,
            relationType: 'parent'
        ));

        $childrenIds = $this->repository->getChildrenIds($parentTermId);

        $this->assertCount(2, $childrenIds);
        $this->assertContains($child1Id, $childrenIds);
        $this->assertContains($child2Id, $childrenIds);
    }

    public function test_can_get_ancestor_ids(): void
    {
        $grandparentId = $this->generateUuid();
        $parentId = $this->generateUuid();
        $childId = $this->generateUuid();

        // child -> parent
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $childId,
            toTermId: $parentId,
            relationType: 'parent'
        ));

        // parent -> grandparent
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $parentId,
            toTermId: $grandparentId,
            relationType: 'parent'
        ));

        $ancestors = $this->repository->getAncestorIds($childId);

        $this->assertCount(2, $ancestors);
        $this->assertEquals($parentId, $ancestors[0]);
        $this->assertEquals($grandparentId, $ancestors[1]);
    }

    public function test_can_get_descendant_ids(): void
    {
        $grandparentId = $this->generateUuid();
        $parentId = $this->generateUuid();
        $childId = $this->generateUuid();

        // child -> parent
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $childId,
            toTermId: $parentId,
            relationType: 'parent'
        ));

        // parent -> grandparent
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $parentId,
            toTermId: $grandparentId,
            relationType: 'parent'
        ));

        $descendants = $this->repository->getDescendantIds($grandparentId);

        $this->assertCount(2, $descendants);
        $this->assertContains($parentId, $descendants);
        $this->assertContains($childId, $descendants);
    }

    public function test_can_calculate_depth(): void
    {
        $grandparentId = $this->generateUuid();
        $parentId = $this->generateUuid();
        $childId = $this->generateUuid();

        // child -> parent
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $childId,
            toTermId: $parentId,
            relationType: 'parent'
        ));

        // parent -> grandparent
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $parentId,
            toTermId: $grandparentId,
            relationType: 'parent'
        ));

        $this->assertEquals(0, $this->repository->calculateDepth($grandparentId));
        $this->assertEquals(1, $this->repository->calculateDepth($parentId));
        $this->assertEquals(2, $this->repository->calculateDepth($childId));
    }

    public function test_can_find_by_term_id(): void
    {
        $termId = $this->generateUuid();
        $otherTermId = $this->generateUuid();

        // Relation where termId is source
        $relation1 = new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $termId,
            toTermId: $otherTermId,
            relationType: 'parent'
        );
        $this->repository->save($relation1);

        // Relation where termId is target
        $thirdTermId = $this->generateUuid();
        $relation2 = new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $thirdTermId,
            toTermId: $termId,
            relationType: 'related'
        );
        $this->repository->save($relation2);

        $relations = $this->repository->findByTermId($termId);

        $this->assertCount(2, $relations);
    }

    public function test_delete_all_by_term_id_removes_all_relations(): void
    {
        $termId = $this->generateUuid();
        $otherTermId = $this->generateUuid();
        $thirdTermId = $this->generateUuid();

        // Relation where termId is source
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $termId,
            toTermId: $otherTermId,
            relationType: 'parent'
        ));

        // Relation where termId is target
        $this->repository->save(new TermRelation(
            id: $this->generateUuid(),
            fromTermId: $thirdTermId,
            toTermId: $termId,
            relationType: 'related'
        ));

        $this->repository->deleteAllByTermId($termId);

        $relations = $this->repository->findByTermId($termId);
        $this->assertCount(0, $relations);
    }

    private function createRelation(array $overrides = []): TermRelation
    {
        return new TermRelation(
            id: $overrides['id'] ?? $this->generateUuid(),
            fromTermId: $overrides['fromTermId'] ?? $this->generateUuid(),
            toTermId: $overrides['toTermId'] ?? $this->generateUuid(),
            relationType: $overrides['relationType'] ?? 'parent',
            depth: $overrides['depth'] ?? 0
        );
    }
}
