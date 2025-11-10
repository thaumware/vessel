<?php

namespace App\Taxonomy\Domain\Interfaces;

use App\Taxonomy\Domain\Entities\Vocabulary;
use App\Shared\Domain\DTOs\PaginationParams;
use App\Shared\Domain\DTOs\PaginatedResult;

interface VocabularyRepositoryInterface
{
    public function save(Vocabulary $vocabulary): void;

    public function findById(string $id): ?Vocabulary;

    public function findAll(PaginationParams $params): PaginatedResult;

    public function existsBySlugAndWorkspace(string $slug, ?string $workspaceId): bool;

    public function delete(Vocabulary $vocabulary): void;
}