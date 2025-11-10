<?php

namespace App\Taxonomy\Domain\UseCases\Vocabulary;

use App\Taxonomy\Domain\Entities\Vocabulary;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;
use Thaumware\Support\Uuid\Uuid;

class CreateVocabulary
{
    public function __construct(
        private VocabularyRepositoryInterface $repository
    ) {
    }

    public function execute(string $name, ?string $workspaceId = null): Vocabulary
    {
        // Business rule: generate slug from name
        $slug = $this->generateSlug($name);

        // Business rule: slug must be unique per workspace
        if ($this->repository->existsBySlugAndWorkspace($slug, $workspaceId)) {
            throw new \DomainException("Vocabulary with slug '{$slug}' already exists in this workspace");
        }

        $vocabulary = new Vocabulary(
            id: Uuid::v4(),
            name: $name,
            slug: $slug,
            description: null,
            workspace_id: $workspaceId
        );

        $this->repository->save($vocabulary);

        return $vocabulary;
    }

    private function generateSlug(string $name): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
}
