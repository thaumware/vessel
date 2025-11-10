<?php

namespace App\Taxonomy\Domain\Interfaces;

use App\Taxonomy\Domain\Entities\Vocabulary;

interface VocabularyRepositoryInterface
{
    public function save(Vocabulary $vocabulary): void;

    public function findById(string $id): ?Vocabulary;

    public function findAll(): array;

    public function existsBySlugAndWorkspace(string $slug, ?string $workspaceId): bool;

    public function delete(Vocabulary $vocabulary): void;
}