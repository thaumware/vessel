<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class GetTermTree
{
    public function __construct(
        private TermRepositoryInterface $termRepository
    ) {}

    public function execute(string $vocabularyId, ?string $parentId = null): array
    {
        return $this->termRepository->getTree($vocabularyId, $parentId);
    }
}
