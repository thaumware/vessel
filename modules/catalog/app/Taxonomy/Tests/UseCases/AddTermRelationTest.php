<?php

namespace App\Taxonomy\Tests\UseCases;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Entities\TermRelation;
use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use App\Taxonomy\Domain\UseCases\TermRelation\AddTermRelation;
use App\Taxonomy\Tests\TaxonomyTestCase;

class AddTermRelationTest extends TaxonomyTestCase
{
    /** @var TermRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $termRepository;
    /** @var TermRelationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $relationRepository;
    private AddTermRelation $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->termRepository = $this->createMock(TermRepositoryInterface::class);
        $this->relationRepository = $this->createMock(TermRelationRepositoryInterface::class);
        
        $this->useCase = new AddTermRelation(
            $this->relationRepository,
            $this->termRepository
        );
    }

    public function test_can_add_parent_relation(): void
    {
        $childTermId = $this->generateUuid();
        $parentTermId = $this->generateUuid();
        $relationId = $this->generateUuid();

        $childTerm = $this->createTerm($childTermId);
        $parentTerm = $this->createTerm($parentTermId);

        $this->termRepository
            ->method('findById')
            ->willReturnMap([
                [$childTermId, $childTerm],
                [$parentTermId, $parentTerm],
            ]);

        $this->relationRepository
            ->method('existsRelation')
            ->willReturn(false);

        $this->relationRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (TermRelation $relation) use ($childTermId, $parentTermId) {
                return $relation->getFromTermId() === $childTermId
                    && $relation->getToTermId() === $parentTermId
                    && $relation->getRelationType() === 'parent';
            }));

        $result = $this->useCase->execute(
            id: $relationId,
            fromTermId: $childTermId,
            toTermId: $parentTermId,
            relationType: 'parent'
        );

        $this->assertInstanceOf(TermRelation::class, $result);
        $this->assertEquals($childTermId, $result->getFromTermId());
        $this->assertEquals($parentTermId, $result->getToTermId());
        $this->assertEquals('parent', $result->getRelationType());
    }

    public function test_can_add_related_relation(): void
    {
        $term1Id = $this->generateUuid();
        $term2Id = $this->generateUuid();

        $this->termRepository
            ->method('findById')
            ->willReturnMap([
                [$term1Id, $this->createTerm($term1Id)],
                [$term2Id, $this->createTerm($term2Id)],
            ]);

        $this->relationRepository
            ->method('existsRelation')
            ->willReturn(false);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            fromTermId: $term1Id,
            toTermId: $term2Id,
            relationType: 'related'
        );

        $this->assertEquals('related', $result->getRelationType());
    }

    public function test_can_add_synonym_relation(): void
    {
        $term1Id = $this->generateUuid();
        $term2Id = $this->generateUuid();

        $this->termRepository
            ->method('findById')
            ->willReturnMap([
                [$term1Id, $this->createTerm($term1Id)],
                [$term2Id, $this->createTerm($term2Id)],
            ]);

        $this->relationRepository
            ->method('existsRelation')
            ->willReturn(false);

        $result = $this->useCase->execute(
            id: $this->generateUuid(),
            fromTermId: $term1Id,
            toTermId: $term2Id,
            relationType: 'synonym'
        );

        $this->assertEquals('synonym', $result->getRelationType());
    }

    public function test_throws_exception_when_from_term_not_found(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('One or both terms do not exist');

        $this->termRepository
            ->method('findById')
            ->willReturn(null);

        $this->useCase->execute(
            id: $this->generateUuid(),
            fromTermId: $this->generateUuid(),
            toTermId: $this->generateUuid()
        );
    }

    public function test_throws_exception_when_to_term_not_found(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('One or both terms do not exist');

        $fromTermId = $this->generateUuid();
        
        $this->termRepository
            ->method('findById')
            ->willReturnMap([
                [$fromTermId, $this->createTerm($fromTermId)],
                [$this->generateUuid(), null],
            ]);

        $this->useCase->execute(
            id: $this->generateUuid(),
            fromTermId: $fromTermId,
            toTermId: $this->generateUuid()
        );
    }

    public function test_throws_exception_when_relation_already_exists(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Relation already exists');

        $term1Id = $this->generateUuid();
        $term2Id = $this->generateUuid();

        $this->termRepository
            ->method('findById')
            ->willReturnMap([
                [$term1Id, $this->createTerm($term1Id)],
                [$term2Id, $this->createTerm($term2Id)],
            ]);

        $this->relationRepository
            ->method('existsRelation')
            ->willReturn(true);

        $this->useCase->execute(
            id: $this->generateUuid(),
            fromTermId: $term1Id,
            toTermId: $term2Id
        );
    }

    public function test_throws_exception_when_self_reference(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A term cannot be related to itself');

        $termId = $this->generateUuid();

        $this->termRepository
            ->method('findById')
            ->willReturn($this->createTerm($termId));

        $this->useCase->execute(
            id: $this->generateUuid(),
            fromTermId: $termId,
            toTermId: $termId
        );
    }

    private function createTerm(string $id): Term
    {
        return new Term(
            id: $id,
            name: 'Term ' . substr($id, 0, 8),
            slug: 'term-' . substr($id, 0, 8),
            vocabularyId: $this->generateUuid()
        );
    }
}
