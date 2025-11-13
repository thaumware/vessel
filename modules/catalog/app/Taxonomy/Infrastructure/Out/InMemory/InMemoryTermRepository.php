<?php

namespace App\Taxonomy\Infrastructure\Out\InMemory;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Entities\TermRelation;
use App\Taxonomy\Domain\DTOs\TermTreeNode;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use App\Shared\Domain\DTOs\PaginationParams;
use App\Shared\Domain\DTOs\PaginatedResult;

class InMemoryTermRepository implements TermRepositoryInterface
{
    private array $terms = [];
    private array $termRelations = [];

    public function __construct()
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $dataFile = __DIR__ . '/../Data/terms.php';

        if (file_exists($dataFile)) {
            $data = require $dataFile;

            // Load terms
            foreach ($data['terms'] as $termData) {
                $term = new Term(
                    $termData['id'],
                    $termData['name'],
                    $termData['slug'],
                    $termData['vocabulary_id'],
                    $termData['description'] ?? null,
                    $termData['workspace_id'] ?? null
                );
                $this->terms[$term->getId()] = $term;
            }

            // Load term relations
            foreach ($data['term_relations'] as $relationData) {
                $relation = new TermRelation(
                    $relationData['id'],
                    $relationData['from_term_id'],
                    $relationData['to_term_id'],
                    $relationData['relation_type'],
                    $relationData['depth']
                );
                $this->termRelations[$relation->getId()] = $relation;
            }
        }
    }

    public function save(Term $term): void
    {
        $this->terms[$term->getId()] = $term;
    }

    public function findById(string $id): ?Term
    {
        return $this->terms[$id] ?? null;
    }

    public function findAll(PaginationParams $params): PaginatedResult
    {
        $allTerms = array_values($this->terms);
        $total = count($allTerms);

        // Simple pagination
        $offset = ($params->page - 1) * $params->perPage;
        $paginatedTerms = array_slice($allTerms, $offset, $params->perPage);
        $lastPage = ceil($total / $params->perPage);

        return new PaginatedResult(
            data: $paginatedTerms,
            total: $total,
            page: $params->page,
            perPage: $params->perPage,
            lastPage: $lastPage
        );
    }

    public function findByVocabulary(string $vocabularyId, PaginationParams $params): PaginatedResult
    {
        $filteredTerms = array_filter($this->terms, fn(Term $term) => $term->getVocabularyId() === $vocabularyId);
        $allTerms = array_values($filteredTerms);
        $total = count($allTerms);

        // Simple pagination
        $offset = ($params->page - 1) * $params->perPage;
        $paginatedTerms = array_slice($allTerms, $offset, $params->perPage);
        $lastPage = ceil($total / $params->perPage);

        return new PaginatedResult(
            data: $paginatedTerms,
            total: $total,
            page: $params->page,
            perPage: $params->perPage,
            lastPage: $lastPage
        );
    }

    public function getTree(string $vocabularyId, ?string $parentId = null): array
    {
        $rootTerms = $this->findRootTerms($vocabularyId, $parentId);
        $tree = [];

        foreach ($rootTerms as $term) {
            $tree[] = $this->buildTreeNode($term, 0);
        }

        return $tree;
    }

    private function findRootTerms(string $vocabularyId, ?string $parentId): array
    {
        if ($parentId === null) {
            // Find terms that are not children of any other term
            $childTermIds = array_map(
                fn(TermRelation $relation) => $relation->getFromTermId(),
                array_filter(
                    $this->termRelations,
                    fn(TermRelation $relation) => $relation->getRelationType() === 'parent'
                )
            );

            return array_filter(
                $this->terms,
                fn(Term $term) => $term->getVocabularyId() === $vocabularyId &&
                                 !in_array($term->getId(), $childTermIds)
            );
        } else {
            // Find direct children of the parent
            $childRelations = array_filter(
                $this->termRelations,
                fn(TermRelation $relation) => $relation->getToTermId() === $parentId &&
                                             $relation->getRelationType() === 'parent'
            );

            $childTermIds = array_map(
                fn(TermRelation $relation) => $relation->getFromTermId(),
                $childRelations
            );

            return array_filter(
                $this->terms,
                fn(Term $term) => in_array($term->getId(), $childTermIds)
            );
        }
    }

    private function buildTreeNode(Term $term, int $depth): TermTreeNode
    {
        $children = $this->findChildTerms($term->getId());
        $childNodes = [];

        foreach ($children as $child) {
            $childNodes[] = $this->buildTreeNode($child, $depth + 1);
        }

        return new TermTreeNode(
            id: $term->getId(),
            name: $term->getName(),
            slug: $term->getSlug(),
            description: $term->getDescription(),
            depth: $depth,
            children: $childNodes
        );
    }

    private function findChildTerms(string $parentId): array
    {
        $childRelations = array_filter(
            $this->termRelations,
            fn(TermRelation $relation) => $relation->getToTermId() === $parentId &&
                                         $relation->getRelationType() === 'parent'
        );

        $childTermIds = array_map(
            fn(TermRelation $relation) => $relation->getFromTermId(),
            $childRelations
        );

        return array_filter(
            $this->terms,
            fn(Term $term) => in_array($term->getId(), $childTermIds)
        );
    }

    public function getBreadcrumb(string $termId): string
    {
        $term = $this->findById($termId);
        if (!$term) {
            return '';
        }

        $breadcrumb = [];
        $currentTerm = $term;

        // Traverse up the hierarchy to build the breadcrumb
        while ($currentTerm) {
            array_unshift($breadcrumb, $currentTerm->getName());
            $parentId = $this->findParentId($currentTerm->getId());
            $currentTerm = $parentId ? $this->findById($parentId) : null;
        }

        return implode('/', $breadcrumb);
    }

    private function findParentId(string $termId): ?string
    {
        foreach ($this->termRelations as $relation) {
            if ($relation->getFromTermId() === $termId && $relation->getRelationType() === 'parent') {
                return $relation->getToTermId();
            }
        }
        return null;
    }

    public function delete(Term $term): void
    {
        unset($this->terms[$term->getId()]);
    }
}