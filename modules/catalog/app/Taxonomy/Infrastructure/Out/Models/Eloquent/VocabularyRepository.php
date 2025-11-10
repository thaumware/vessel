<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Shared\Domain\DTOs\PaginationParams;
use App\Shared\Domain\DTOs\PaginatedResult;
use App\Taxonomy\Domain\Entities\Vocabulary;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;

class VocabularyRepository implements VocabularyRepositoryInterface
{
    public function save(Vocabulary $vocabulary): void
    {
        $model = VocabularyModel::find($vocabulary->getId()) ?? new VocabularyModel();
        $model->id = $vocabulary->getId();
        $model->name = $vocabulary->getName();
        $model->slug = $vocabulary->getSlug();
        $model->description = $vocabulary->getDescription();
        $model->workspace_id = $vocabulary->getWorkspaceId();
        $model->save();
    }

    public function findById(string $id): ?Vocabulary
    {
        $model = VocabularyModel::find($id);

        if (!$model) {
            return null;
        }

        return new Vocabulary(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            workspace_id: $model->workspace_id
        );
    }

    public function findAll(PaginationParams $params): PaginatedResult
    {
        $query = VocabularyModel::query();

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
            return (new Vocabulary(
                id: $model->id,
                name: $model->name,
                slug: $model->slug,
                description: $model->description,
                workspace_id: $model->workspace_id
            ))->toArray();
        })->all();

        return PaginatedResult::fromArray($data, $total, $params);
    }

    public function existsBySlugAndWorkspace(string $slug, ?string $workspaceId): bool
    {
        return VocabularyModel::where('slug', $slug)
            ->when($workspaceId, fn($q) => $q->where('workspace_id', $workspaceId))
            ->when(!$workspaceId, fn($q) => $q->whereNull('workspace_id'))
            ->exists();
    }

    public function delete(Vocabulary $vocabulary): void
    {
        $model = VocabularyModel::find($vocabulary->getId());

        if ($model) {
            $model->delete();
        }
    }
}