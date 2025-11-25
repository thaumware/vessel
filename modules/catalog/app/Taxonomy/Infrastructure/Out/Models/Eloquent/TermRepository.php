<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use App\Shared\Domain\DTOs\PaginationParams;
use App\Shared\Domain\DTOs\PaginatedResult;

class TermRepository implements TermRepositoryInterface
{
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

        return new Term(
            id: $term->id,
            name: $term->name,
            slug: $term->slug,
            vocabularyId: $term->vocabulary_id,
            description: $term->description,
            workspaceId: $term->workspace_id
        );
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
            return (new Term(
                id: $model->id,
                name: $model->name,
                slug: $model->slug,
                vocabularyId: $model->vocabulary_id,
                description: $model->description,
                workspaceId: $model->workspace_id
            ))->toArray();
        })->all();

        return PaginatedResult::fromArray($data, $total, $params);
    }

    public function findByVocabulary(string $vocabularyId, PaginationParams $params): PaginatedResult
    {
        $query = TermModel::where('vocabulary_id', $vocabularyId);

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
            return (new Term(
                id: $model->id,
                name: $model->name,
                slug: $model->slug,
                vocabularyId: $model->vocabulary_id,
                description: $model->description,
                workspaceId: $model->workspace_id
            ))->toArray();
        })->all();

        return PaginatedResult::fromArray($data, $total, $params);
    }

    public function getTree(string $vocabularyId, ?string $parentId = null): array
    {
        // TODO: Implement tree structure when parent_id field is added to terms table
        // For now, return all terms in vocabulary as flat array
        $terms = TermModel::where('vocabulary_id', $vocabularyId)->get();

        return $terms->map(function ($model) {
            return (new Term(
                id: $model->id,
                name: $model->name,
                slug: $model->slug,
                vocabularyId: $model->vocabulary_id,
                description: $model->description,
                workspaceId: $model->workspace_id
            ))->toArray();
        })->all();
    }

    public function getBreadcrumb(string $termId): string
    {
        $term = $this->findById($termId);
        if (!$term) {
            return '';
        }

        $breadcrumb = [];
        $currentTerm = $term;

        // Traverse up the hierarchy using term relationships
        while ($currentTerm) {
            array_unshift($breadcrumb, $currentTerm->getName());
            $parentRelation = TermRelationshipModel::where('child_term_id', $currentTerm->getId())->first();
            $currentTerm = $parentRelation ? $this->findById($parentRelation->parent_term_id) : null;
        }

        return implode('/', $breadcrumb);
    }

    public function delete(Term $term): void
    {
        $termModel = TermModel::find($term->getId());

        if ($termModel) {
            $termModel->delete();
        }
    }
}