<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class CreateTerm
{
    private const MAX_SLUG_ATTEMPTS = 100;

    public function __construct(
        private TermRepositoryInterface $repository
    ) {
    }

    public function execute(
        string $id,
        string $name,
        string $vocabularyId,
        ?string $description = null
    ): Term {
        $baseSlug = $this->generateSlug($name);
        
        if (empty($baseSlug)) {
            $baseSlug = 'term';
        }
        
        $slug = $this->ensureUniqueSlug($baseSlug, $vocabularyId);

        $term = new Term(
            id: $id,
            name: $name,
            slug: $slug,
            vocabularyId: $vocabularyId,
            description: $description
        );

        $this->repository->save($term);

        return $term;
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        // Remove consecutive dashes
        $slug = preg_replace('/-+/', '-', $slug);
        return $slug;
    }

    private function ensureUniqueSlug(string $baseSlug, string $vocabularyId): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while ($this->repository->findBySlugAndVocabulary($slug, $vocabularyId) !== null) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            
            if ($counter > self::MAX_SLUG_ATTEMPTS) {
                // Add timestamp to guarantee uniqueness
                $slug = $baseSlug . '-' . time() . '-' . mt_rand(1000, 9999);
                break;
            }
        }

        return $slug;
    }
}
