<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;
use App\Shared\Domain\DTOs\PaginationParams;
use App\Shared\Domain\DTOs\PaginatedResult;

class TermRepository implements TermRepositoryInterface
{
    public function __construct(
        private ?TermRelationRepositoryInterface $relationRepository = null
    ) {
        // Lazy load relation repository if not injected
        if ($this->relationRepository === null) {
            $this->relationRepository = app(TermRelationRepositoryInterface::class);
        }
    }

    public function save(Term $term): void
    {
        $termModel = TermModel::find($term->getId()) ?? new TermModel();

        $termModel->id = $term->getId();
        $termModel->name = $term->getName();
        $termModel->slug = $term->getSlug();
        $termModel->vocabulary_id = $term->getVocabularyId();
        $termModel->description = $term->getDescription();
        $termModel->workspace_id = $term->getWorkspaceId();
        $termModel->save();
    }

    public function findById(string $id): ?Term
    {
        $term = TermModel::find($id);

        if (!$term) {
            return null;
        }

        return $this->mapToEntity($term);
    }

    public function findBySlugAndVocabulary(string $slug, string $vocabularyId): ?Term
    {
        // Include soft-deleted to avoid unique constraint violations
        $term = TermModel::withTrashed()
            ->where('slug', $slug)
            ->where('vocabulary_id', $vocabularyId)
            ->first();

        if (!$term) {
            return null;
        }

        return $this->mapToEntity($term);
    }

    private function mapToEntity(TermModel $model): Term
    {
        return new Term(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            vocabularyId: $model->vocabulary_id,
            description: $model->description,
            workspaceId: $model->workspace_id
        );
    }

    private function mapWithRelations(array $termArray): array
    {
        $termId = $termArray['id'];
        $termArray['parent_id'] = $this->relationRepository->getParentId($termId);
        $termArray['children_ids'] = $this->relationRepository->getChildrenIds($termId);

        return $termArray;
    }

    public function findAll(PaginationParams $params): PaginatedResult
    {
        $query = TermModel::query();

        // Apply sorting
        if ($params->sortBy) {
            $query->orderBy($params->sortBy, $params->sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply pagination
        $models = $query
            ->skip($params->getOffset())
            ->take($params->getLimit())
            ->get();

        // Map to arrays
        $data = $models->map(function ($model) {
            return $this->mapWithRelations($this->mapToEntity($model)->toArray());
        })->all();

        return PaginatedResult::fromArray($data, $total, $params);
    }

    public function findByVocabulary(string $vocabularyId, PaginationParams $params, ?string $workspaceId = null): PaginatedResult
    {
        $query = TermModel::where('vocabulary_id', $vocabularyId);

        if ($workspaceId !== null) {
            $query->where(function ($q) use ($workspaceId) {
                $q->whereNull('workspace_id')->orWhere('workspace_id', $workspaceId);
            });
        }

        // Apply sorting
        if ($params->sortBy) {
            $query->orderBy($params->sortBy, $params->sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply pagination
        $models = $query
            ->skip($params->getOffset())
            ->take($params->getLimit())
            ->get();

        // Map to arrays
        $data = $models->map(function ($model) {
            return $this->mapWithRelations($this->mapToEntity($model)->toArray());
        })->all();

        return PaginatedResult::fromArray($data, $total, $params);
    }

    public function getTree(string $vocabularyId, ?string $parentId = null, ?int $maxDepth = null): array
    {
        $visited = [];
        return $this->buildTree($vocabularyId, $parentId, $visited, 0, $maxDepth);
    }

    private function buildTree(string $vocabularyId, ?string $parentId, array &$visited, int $depth, ?int $maxDepth): array
    {
        if ($maxDepth !== null && $depth >= $maxDepth) {
            return [];
        }
        // Get root terms or children of parentId
        if ($parentId === null) {
            // Get all terms in vocabulary
            $allTermIds = TermModel::where('vocabulary_id', $vocabularyId)
                ->pluck('id')
                ->all();

            // Get terms that have a parent relation (they are children)
            $childTermIds = TermRelationModel::whereIn('from_term_id', $allTermIds)
                ->where('relation_type', 'parent')
                ->pluck('from_term_id')
                ->all();

            // Root terms are those not in childTermIds
            $rootTermIds = array_diff($allTermIds, $childTermIds);

            $terms = TermModel::whereIn('id', $rootTermIds)
                ->where('vocabulary_id', $vocabularyId)
                ->orderBy('name')
                ->get();
        } else {
            // Get children of parentId
            $childrenIds = $this->relationRepository->getChildrenIds($parentId);

            $terms = TermModel::whereIn('id', $childrenIds)
                ->where('vocabulary_id', $vocabularyId)
                ->orderBy('name')
                ->get();
        }

        return $terms->map(function ($model) use ($vocabularyId, &$visited, $depth, $maxDepth) {
            // Break potential cycles
            if (in_array($model->id, $visited, true)) {
                return null;
            }
            $visited[] = $model->id;

            $term = $this->mapWithRelations($this->mapToEntity($model)->toArray());
            $term['children'] = $this->buildTree($vocabularyId, $model->id, $visited, $depth + 1, $maxDepth);
            return $term;
        })->filter()->all();
    }

    public function getBreadcrumb(string $termId): string
    {
        $term = $this->findById($termId);
        if (!$term) {
            return '';
        }

        $breadcrumb = [];
        $currentTerm = $term;

        // Traverse up the hierarchy using term relations
        while ($currentTerm) {
            array_unshift($breadcrumb, $currentTerm->getName());
            $parentId = $this->relationRepository->getParentId($currentTerm->getId());
            $currentTerm = $parentId ? $this->findById($parentId) : null;
        }

        return implode('/', $breadcrumb);
    }

    public function delete(Term $term): void
    {
        $termModel = TermModel::find($term->getId());

        if ($termModel) {
            // Also delete all relations involving this term
            $this->relationRepository->deleteAllByTermId($term->getId());
            $termModel->delete();
        }
    }
}