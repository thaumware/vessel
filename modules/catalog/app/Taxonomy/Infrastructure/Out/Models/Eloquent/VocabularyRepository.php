<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

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

    public function findAll(): array
    {
        return VocabularyModel::all()
            ->map(fn($model) => (new Vocabulary(
                id: $model->id,
                name: $model->name,
                slug: $model->slug,
                description: $model->description,
                workspace_id: $model->workspace_id
            ))->toArray())
            ->all();
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