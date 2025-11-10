<?php

namespace App\Taxonomy\Domain\UseCases\TermRelation;

use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;

class RemoveTermRelation
{
    public function __construct(
        private TermRelationRepositoryInterface $relationRepository
    ) {}

    public function execute(
        string $fromTermId,
        string $toTermId,
        string $relationType = 'parent'
    ): bool {
        if (!$this->relationRepository->existsRelation($fromTermId, $toTermId, $relationType)) {
            return false;
        }

        $this->relationRepository->deleteByTerms($fromTermId, $toTermId, $relationType);

        return true;
    }
}
