<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class GetTerm
{
    public function __construct(
        private TermRepositoryInterface $repository
    ) {}

    public function execute(string $id): ?Term
    {
        return $this->repository->findById($id);
    }
}
