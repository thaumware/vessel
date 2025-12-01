<?php

namespace App\Taxonomy\Infrastructure\Out\InMemory;

use App\Taxonomy\Domain\Entities\TermRelation;
use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;

class InMemoryTermRelationRepository implements TermRelationRepositoryInterface
{
    private array $relations = [];

    public function __construct()
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $dataFile = __DIR__ . '/../Data/terms.php';

        if (file_exists($dataFile)) {
            $data = require $dataFile;

            foreach ($data['term_relations'] ?? [] as $relationData) {
                $relation = new TermRelation(
                    $relationData['id'],
                    $relationData['from_term_id'],
                    $relationData['to_term_id'],
                    $relationData['relation_type'],
                    $relationData['depth'] ?? 0
                );
                $this->relations[$relation->getId()] = $relation;
            }
        }
    }

    public function save(TermRelation $relation): void
    {
        $this->relations[$relation->getId()] = $relation;
    }

    public function findById(string $id): ?TermRelation
    {
        return $this->relations[$id] ?? null;
    }

    public function findByTermId(string $termId): array
    {
        return array_filter(
            $this->relations,
            fn(TermRelation $r) => $r->getFromTermId() === $termId || $r->getToTermId() === $termId
        );
    }

    public function findByFromTermId(string $termId, ?string $relationType = null): array
    {
        return array_values(array_filter(
            $this->relations,
            fn(TermRelation $r) =>
                $r->getFromTermId() === $termId &&
                ($relationType === null || $r->getRelationType() === $relationType)
        ));
    }

    public function findByToTermId(string $termId, ?string $relationType = null): array
    {
        return array_values(array_filter(
            $this->relations,
            fn(TermRelation $r) =>
                $r->getToTermId() === $termId &&
                ($relationType === null || $r->getRelationType() === $relationType)
        ));
    }

    public function existsRelation(string $fromTermId, string $toTermId, string $relationType): bool
    {
        foreach ($this->relations as $relation) {
            if (
                $relation->getFromTermId() === $fromTermId &&
                $relation->getToTermId() === $toTermId &&
                $relation->getRelationType() === $relationType
            ) {
                return true;
            }
        }
        return false;
    }

    public function delete(TermRelation $relation): void
    {
        unset($this->relations[$relation->getId()]);
    }

    public function deleteByTerms(string $fromTermId, string $toTermId, string $relationType): void
    {
        foreach ($this->relations as $id => $relation) {
            if (
                $relation->getFromTermId() === $fromTermId &&
                $relation->getToTermId() === $toTermId &&
                $relation->getRelationType() === $relationType
            ) {
                unset($this->relations[$id]);
                return;
            }
        }
    }

    public function deleteAllByTermId(string $termId): void
    {
        $this->relations = array_filter(
            $this->relations,
            fn(TermRelation $r) => $r->getFromTermId() !== $termId && $r->getToTermId() !== $termId
        );
    }

    public function getParentId(string $termId): ?string
    {
        foreach ($this->relations as $relation) {
            if ($relation->getFromTermId() === $termId && $relation->getRelationType() === 'parent') {
                return $relation->getToTermId();
            }
        }
        return null;
    }

    public function getChildrenIds(string $termId): array
    {
        $children = [];
        foreach ($this->relations as $relation) {
            if ($relation->getToTermId() === $termId && $relation->getRelationType() === 'parent') {
                $children[] = $relation->getFromTermId();
            }
        }
        return $children;
    }

    public function getAncestorIds(string $termId): array
    {
        $ancestors = [];
        $currentId = $termId;

        while ($parentId = $this->getParentId($currentId)) {
            $ancestors[] = $parentId;
            $currentId = $parentId;
        }

        return $ancestors;
    }

    public function getDescendantIds(string $termId): array
    {
        $descendants = [];
        $childrenIds = $this->getChildrenIds($termId);

        foreach ($childrenIds as $childId) {
            $descendants[] = $childId;
            $descendants = array_merge($descendants, $this->getDescendantIds($childId));
        }

        return $descendants;
    }

    public function getRootTermIds(string $vocabularyId): array
    {
        // This requires access to terms - in real implementation would need TermRepository
        // For InMemory, we return empty - this method should be used via TermRepository
        return [];
    }

    public function calculateDepth(string $termId): int
    {
        return count($this->getAncestorIds($termId));
    }
}
