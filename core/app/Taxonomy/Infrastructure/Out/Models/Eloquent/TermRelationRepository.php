<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Taxonomy\Domain\Entities\TermRelation;
use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TermRelationRepository implements TermRelationRepositoryInterface
{
    public function save(TermRelation $relation): void
    {
        $model = TermRelationModel::find($relation->getId()) ?? new TermRelationModel();

        $model->id = $relation->getId();
        $model->from_term_id = $relation->getFromTermId();
        $model->to_term_id = $relation->getToTermId();
        $model->relation_type = $relation->getRelationType();
        $model->depth = $relation->getDepth();
        $model->save();
    }

    public function findById(string $id): ?TermRelation
    {
        $model = TermRelationModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function findByTermId(string $termId): array
    {
        $models = TermRelationModel::where('from_term_id', $termId)
            ->orWhere('to_term_id', $termId)
            ->get();

        return $models->map(fn($m) => $this->mapToEntity($m))->all();
    }

    public function findByFromTermId(string $termId, ?string $relationType = null): array
    {
        $query = TermRelationModel::where('from_term_id', $termId);

        if ($relationType !== null) {
            $query->where('relation_type', $relationType);
        }

        return $query->get()->map(fn($m) => $this->mapToEntity($m))->all();
    }

    public function findByToTermId(string $termId, ?string $relationType = null): array
    {
        $query = TermRelationModel::where('to_term_id', $termId);

        if ($relationType !== null) {
            $query->where('relation_type', $relationType);
        }

        return $query->get()->map(fn($m) => $this->mapToEntity($m))->all();
    }

    public function existsRelation(string $fromTermId, string $toTermId, string $relationType): bool
    {
        return TermRelationModel::where('from_term_id', $fromTermId)
            ->where('to_term_id', $toTermId)
            ->where('relation_type', $relationType)
            ->exists();
    }

    public function delete(TermRelation $relation): void
    {
        TermRelationModel::destroy($relation->getId());
    }

    public function deleteByTerms(string $fromTermId, string $toTermId, string $relationType): void
    {
        TermRelationModel::where('from_term_id', $fromTermId)
            ->where('to_term_id', $toTermId)
            ->where('relation_type', $relationType)
            ->delete();
    }

    public function deleteAllByTermId(string $termId): void
    {
        TermRelationModel::where('from_term_id', $termId)
            ->orWhere('to_term_id', $termId)
            ->delete();
    }

    /**
     * Get the parent term ID of a given term (via 'parent' relation)
     */
    public function getParentId(string $termId): ?string
    {
        // In parent relation: from_term_id is child, to_term_id is parent
        $relation = TermRelationModel::where('from_term_id', $termId)
            ->where('relation_type', 'parent')
            ->first();

        return $relation?->to_term_id;
    }

    /**
     * Get all children term IDs of a given term (via 'parent' relation)
     */
    public function getChildrenIds(string $termId): array
    {
        // Children have relations where to_term_id = this term and relation_type = parent
        return TermRelationModel::where('to_term_id', $termId)
            ->where('relation_type', 'parent')
            ->pluck('from_term_id')
            ->all();
    }

    /**
     * Get all ancestor term IDs (parents, grandparents, etc.)
     */
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

    /**
     * Get all descendant term IDs recursively
     */
    public function getDescendantIds(string $termId): array
    {
        $visited = [];
        return $this->collectDescendants($termId, $visited);
    }

    private function collectDescendants(string $termId, array &$visited): array
    {
        if (in_array($termId, $visited, true)) {
            return [];
        }
        $visited[] = $termId;

        $descendants = [];
        $childrenIds = $this->getChildrenIds($termId);

        foreach ($childrenIds as $childId) {
            if (in_array($childId, $visited, true)) {
                continue;
            }
            $descendants[] = $childId;
            $descendants = array_merge($descendants, $this->collectDescendants($childId, $visited));
        }

        return $descendants;
    }

    /**
     * Get root term IDs for a vocabulary (terms without parent)
     */
    public function getRootTermIds(string $vocabularyId): array
    {
        // Get all terms in vocabulary
        $allTermIds = TermModel::where('vocabulary_id', $vocabularyId)
            ->pluck('id')
            ->all();

        // Get terms that have a parent relation
        $termsWithParent = TermRelationModel::whereIn('from_term_id', $allTermIds)
            ->where('relation_type', 'parent')
            ->pluck('from_term_id')
            ->all();

        // Root terms are those without a parent
        return array_diff($allTermIds, $termsWithParent);
    }

    /**
     * Calculate the depth of a term in the hierarchy
     */
    public function calculateDepth(string $termId): int
    {
        return count($this->getAncestorIds($termId));
    }

    private function mapToEntity(TermRelationModel $model): TermRelation
    {
        return new TermRelation(
            id: $model->id,
            fromTermId: $model->from_term_id,
            toTermId: $model->to_term_id,
            relationType: $model->relation_type,
            depth: $model->depth
        );
    }
}
