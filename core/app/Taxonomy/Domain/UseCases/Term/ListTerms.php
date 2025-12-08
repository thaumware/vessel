<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Shared\Domain\DTOs\PaginatedResult;
use App\Shared\Domain\DTOs\PaginationParams;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class ListTerms
{
    public function __construct(
        private TermRepositoryInterface $repository
    ) {}

    public function execute(PaginationParams $params, ?string $vocabularyId = null, ?string $workspaceId = null): PaginatedResult
    {
        if ($vocabularyId) {
            return $this->repository->findByVocabulary($vocabularyId, $params, $workspaceId);
        }

        return $this->repository->findAll($params);
    }
}
