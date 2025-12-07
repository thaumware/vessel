<?php

namespace App\Taxonomy\Domain\UseCases\Vocabulary;

use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class GetVocabularyWithTreeBySlug
{
    public function __construct(
        private VocabularyRepositoryInterface $vocabularyRepository,
        private TermRepositoryInterface $termRepository,
    ) {
    }

    public function execute(string $slug, ?string $workspaceId = null): ?array
    {
        $vocabulary = $this->vocabularyRepository->findBySlug($slug, $workspaceId);

        if (!$vocabulary) {
            return null;
        }

        $tree = $this->termRepository->getTree($vocabulary->getId());

        return [
            'vocabulary' => $vocabulary->toArray(),
            'terms' => $tree,
        ];
    }
}
