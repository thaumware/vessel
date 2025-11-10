<?php

namespace App\Taxonomy\Domain\UseCases\Vocabulary;

use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;

class ListVocabularies
{
    public function __construct(
        private VocabularyRepositoryInterface $repository
    ) {
    }

    public function execute(): array
    {
        return $this->repository->findAll();
    }
}
