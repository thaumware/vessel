<?php

namespace App\Taxonomy\Domain\Interfaces;

use App\Taxonomy\Domain\Entities\Term;

interface TermRepositoryInterface
{
    public function save(Term $term): void;

    public function findById(string $id): ?Term;

    public function findAll(): array;

    public function findByVocabulary(string $vocabularyId): array;

    public function delete(Term $term): void;
}