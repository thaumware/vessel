<?php

namespace App\Taxonomy\Domain\UseCases\TermRelation;

use App\Taxonomy\Domain\Entities\TermRelation;
use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use Thaumware\Support\Uuid\Uuid;

class AddTermRelation
{
    public function __construct(
        private TermRelationRepositoryInterface $relationRepository,
        private TermRepositoryInterface $termRepository
    ) {
    }

    public function execute(
        string $fromTermId,
        string $toTermId,
        string $relationType = 'parent'
    ): TermRelation {
        // Business rule: verify both terms exist
        $fromTerm = $this->termRepository->findById($fromTermId);
        $toTerm = $this->termRepository->findById($toTermId);

        if (!$fromTerm || !$toTerm) {
            throw new \DomainException("One or both terms do not exist");
        }

        // Business rule: prevent duplicate relations
        if ($this->relationRepository->existsRelation($fromTermId, $toTermId, $relationType)) {
            throw new \DomainException("Relation already exists");
        }

        // Business rule: prevent self-reference
        if ($fromTermId === $toTermId) {
            throw new \DomainException("A term cannot be related to itself");
        }

        $relation = new TermRelation(
            id: Uuid::v4(),
            from_term_id: $fromTermId,
            to_term_id: $toTermId,
            relation_type: $relationType,
            depth: 0 // Can be calculated later for hierarchies
        );

        $this->relationRepository->save($relation);

        return $relation;
    }
}
