<?php

namespace App\Taxonomy\Domain\UseCases\TermRelation;

use App\Taxonomy\Domain\Entities\TermRelation;
use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class AddTermRelation
{
    public function __construct(
        private TermRelationRepositoryInterface $relationRepository,
        private TermRepositoryInterface $termRepository
    ) {
    }

    public function execute(
        string $id,
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

        // Business rule: prevent cycles in parent relations
        if ($relationType === 'parent') {
            $descendants = $this->relationRepository->getDescendantIds($fromTermId);
            if (in_array($toTermId, $descendants, true)) {
                throw new \DomainException('Cycle detected: the target term is a descendant of the source term');
            }
        }

        $relation = new TermRelation(
            id: $id,
            fromTermId: $fromTermId,
            toTermId: $toTermId,
            relationType: $relationType,
            depth: 0 // Can be calculated later for hierarchies
        );

        $this->relationRepository->save($relation);

        return $relation;
    }
}
