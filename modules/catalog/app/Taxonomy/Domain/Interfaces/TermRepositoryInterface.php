<?php

namespace App\Taxonomy\Domain\Interfaces;

use App\Taxonomy\Domain\Entities\Term;
use App\Shared\Domain\DTOs\PaginationParams;
use App\Shared\Domain\DTOs\PaginatedResult;

interface TermRepositoryInterface
{
    public function save(Term $term): void;

    public function findById(string $id): ?Term;

    public function findAll(PaginationParams $params): PaginatedResult;

    public function findByVocabulary(string $vocabularyId, PaginationParams $params): PaginatedResult;

    public function getTree(string $vocabularyId, ?string $parentId = null): array;

    public function delete(Term $term): void;
}