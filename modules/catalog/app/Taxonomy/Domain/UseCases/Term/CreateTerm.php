<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use Thaumware\Support\Uuid\Uuid;

class CreateTerm
{
    public function __construct(
        private TermRepositoryInterface $repository
    ) {}

    public function execute(string $name, string $vocabularyId): Term
    {
        $term = new Term(
            id: Uuid::v4(),
            name: $name,
            vocabulary_id: $vocabularyId
        );

        $this->repository->save($term);

        return $term;
    }
}
