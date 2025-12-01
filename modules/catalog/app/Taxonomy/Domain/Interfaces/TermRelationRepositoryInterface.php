<?php

namespace App\Taxonomy\Domain\Interfaces;

use App\Taxonomy\Domain\Entities\TermRelation;

interface TermRelationRepositoryInterface
{
    public function save(TermRelation $relation): void;

    public function findById(string $id): ?TermRelation;

    public function findByTermId(string $termId): array;

    public function findByFromTermId(string $termId, ?string $relationType = null): array;

    public function findByToTermId(string $termId, ?string $relationType = null): array;

    public function existsRelation(string $fromTermId, string $toTermId, string $relationType): bool;

    public function delete(TermRelation $relation): void;

    public function deleteByTerms(string $fromTermId, string $toTermId, string $relationType): void;

    public function deleteAllByTermId(string $termId): void;

    /**
     * Get the parent term ID of a given term (via 'parent' relation)
     */
    public function getParentId(string $termId): ?string;

    /**
     * Get all children term IDs of a given term
     */
    public function getChildrenIds(string $termId): array;

    /**
     * Get all ancestor term IDs (parents, grandparents, etc.)
     */
    public function getAncestorIds(string $termId): array;

    /**
     * Get all descendant term IDs recursively
     */
    public function getDescendantIds(string $termId): array;

    /**
     * Calculate the depth of a term in the hierarchy
     */
    public function calculateDepth(string $termId): int;
}
