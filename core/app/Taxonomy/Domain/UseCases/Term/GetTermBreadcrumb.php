<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class GetTermBreadcrumb
{
    public function __construct(
        private TermRepositoryInterface $termRepository
    ) {}

    public function execute(string $termId): string
    {
        return $this->termRepository->getBreadcrumb($termId);
    }
}