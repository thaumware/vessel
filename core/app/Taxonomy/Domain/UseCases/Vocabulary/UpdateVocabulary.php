<?php

namespace App\Taxonomy\Domain\UseCases\Vocabulary;

use App\Taxonomy\Domain\Entities\Vocabulary;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;

class UpdateVocabulary
{
    public function __construct(
        private VocabularyRepositoryInterface $repository
    ) {
    }

    public function execute(
        string $id,
        string $name
    ): ?Vocabulary {
        $vocabulary = $this->repository->findById($id);

        if (!$vocabulary) {
            return null;
        }

        $updated = $vocabulary;

        $updated->setName($name);

        $this->repository->save($updated);

        return $updated;
    }
}
