<?php

namespace App\Taxonomy\Tests\UseCases;

use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;
use App\Taxonomy\Domain\UseCases\TermRelation\RemoveTermRelation;
use App\Taxonomy\Tests\TaxonomyTestCase;

class RemoveTermRelationTest extends TaxonomyTestCase
{
    /** @var TermRelationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $relationRepository;
    private RemoveTermRelation $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->relationRepository = $this->createMock(TermRelationRepositoryInterface::class);
        $this->useCase = new RemoveTermRelation($this->relationRepository);
    }

    public function test_can_remove_existing_relation(): void
    {
        $fromTermId = $this->generateUuid();
        $toTermId = $this->generateUuid();

        $this->relationRepository
            ->method('existsRelation')
            ->with($fromTermId, $toTermId, 'parent')
            ->willReturn(true);

        $this->relationRepository
            ->expects($this->once())
            ->method('deleteByTerms')
            ->with($fromTermId, $toTermId, 'parent');

        $result = $this->useCase->execute(
            fromTermId: $fromTermId,
            toTermId: $toTermId,
            relationType: 'parent'
        );

        $this->assertTrue($result);
    }

    public function test_returns_false_when_relation_not_found(): void
    {
        $fromTermId = $this->generateUuid();
        $toTermId = $this->generateUuid();

        $this->relationRepository
            ->method('existsRelation')
            ->willReturn(false);

        $this->relationRepository
            ->expects($this->never())
            ->method('deleteByTerms');

        $result = $this->useCase->execute(
            fromTermId: $fromTermId,
            toTermId: $toTermId,
            relationType: 'parent'
        );

        $this->assertFalse($result);
    }

    public function test_can_remove_related_type_relation(): void
    {
        $fromTermId = $this->generateUuid();
        $toTermId = $this->generateUuid();

        $this->relationRepository
            ->method('existsRelation')
            ->with($fromTermId, $toTermId, 'related')
            ->willReturn(true);

        $result = $this->useCase->execute(
            fromTermId: $fromTermId,
            toTermId: $toTermId,
            relationType: 'related'
        );

        $this->assertTrue($result);
    }

    public function test_can_remove_synonym_type_relation(): void
    {
        $fromTermId = $this->generateUuid();
        $toTermId = $this->generateUuid();

        $this->relationRepository
            ->method('existsRelation')
            ->with($fromTermId, $toTermId, 'synonym')
            ->willReturn(true);

        $result = $this->useCase->execute(
            fromTermId: $fromTermId,
            toTermId: $toTermId,
            relationType: 'synonym'
        );

        $this->assertTrue($result);
    }

    public function test_defaults_to_parent_relation_type(): void
    {
        $fromTermId = $this->generateUuid();
        $toTermId = $this->generateUuid();

        $this->relationRepository
            ->expects($this->once())
            ->method('existsRelation')
            ->with($fromTermId, $toTermId, 'parent')
            ->willReturn(true);

        $this->useCase->execute(
            fromTermId: $fromTermId,
            toTermId: $toTermId
        );
    }
}
