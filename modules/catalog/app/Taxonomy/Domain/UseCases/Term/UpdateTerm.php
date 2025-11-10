<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class UpdateTerm
{
    public function __construct(
        private TermRepositoryInterface $repository
    ) {}

    public function execute(string $id, string $name, string $vocabularyId): ?Term
    {
        $term = $this->repository->findById($id);
        
        if (!$term) {
            return null;
        }

        $updated = new Term($id, $name, $vocabularyId);
        $this->repository->save($updated);

        return $updated;
    }
}
