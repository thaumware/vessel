<?php

namespace App\Taxonomy\Domain\UseCases\Vocabulary;

use App\Taxonomy\Domain\Entities\Vocabulary;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;

class GetVocabulary
{
    public function __construct(
        private VocabularyRepositoryInterface $repository
    ) {
    }

    public function execute(string $id): ?Vocabulary
    {
        return $this->repository->findById($id);
    }
}
