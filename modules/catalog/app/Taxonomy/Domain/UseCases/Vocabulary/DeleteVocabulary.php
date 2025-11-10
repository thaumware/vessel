<?php

namespace App\Taxonomy\Domain\UseCases\Vocabulary;

use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;

class DeleteVocabulary
{
    public function __construct(
        private VocabularyRepositoryInterface $repository
    ) {}

    public function execute(string $id): bool
    {
        $vocabulary = $this->repository->findById($id);
        
        if (!$vocabulary) {
            return false;
        }

        $this->repository->delete($vocabulary);
        return true;
    }
}
