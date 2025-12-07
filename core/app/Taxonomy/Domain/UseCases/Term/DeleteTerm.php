<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class DeleteTerm
{
    public function __construct(
        private TermRepositoryInterface $repository
    ) {}

    public function execute(string $id): bool
    {
        $term = $this->repository->findById($id);
        
        if (!$term) {
            return false;
        }

        $this->repository->delete($term);
        return true;
    }
}
