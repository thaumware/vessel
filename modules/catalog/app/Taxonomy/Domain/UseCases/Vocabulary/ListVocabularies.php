<?php

namespace App\Taxonomy\Domain\UseCases\Vocabulary;

use App\Shared\Domain\DTOs\PaginatedResult;
use App\Shared\Domain\DTOs\PaginationParams;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;

class ListVocabularies
{
    public function __construct(
        private VocabularyRepositoryInterface $repository
    ) {
    }

    public function execute(PaginationParams $params): PaginatedResult
    {
        return $this->repository->findAll($params);
    }
}
