<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class ListTerms
{
    public function __construct(
        private TermRepositoryInterface $repository
    ) {}

    public function execute(?string $vocabularyId = null): array
    {
        if ($vocabularyId) {
            return $this->repository->findByVocabulary($vocabularyId);
        }

        return $this->repository->findAll();
    }
}
