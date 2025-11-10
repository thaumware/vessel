<?php

namespace App\Taxonomy\Domain\Interfaces;

use App\Taxonomy\Domain\Entities\TermRelation;

interface TermRelationRepositoryInterface
{
    public function save(TermRelation $relation): void;

    public function findByTermId(string $termId): array;

    public function existsRelation(string $fromTermId, string $toTermId, string $relationType): bool;

    public function delete(TermRelation $relation): void;

    public function deleteByTerms(string $fromTermId, string $toTermId, string $relationType): void;
}
