<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Taxonomy\Domain\Entities\Vocabulary;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;

class VocabularyRepository implements VocabularyRepositoryInterface
{
    public function findById(string $id): ?Vocabulary
    {
        $vocabulary = VocabularyModel::find($id);

        if (!$vocabulary) {
            return null;
        }

        return new Vocabulary(
            id: $vocabulary->id,
            name: $vocabulary->name
        );
    }

    public function save(
        Vocabulary $vocabulary
    ): void {
        $vocabularyModel = VocabularyModel::find($vocabulary->getId()) ?? new VocabularyModel();
        $vocabularyModel->id = $vocabulary->getId();
        $vocabularyModel->name = $vocabulary->getName();
        $vocabularyModel->save();
    }

    public function delete(Vocabulary $vocabulary): void
    {
        $vocabularyModel = VocabularyModel::find($vocabulary->getId());

        if ($vocabularyModel) {
            $vocabularyModel->delete();
        }
    }
}