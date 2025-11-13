<?php

namespace App\Taxonomy\Infrastructure\Out\InMemory;

use App\Taxonomy\Domain\Entities\Vocabulary;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;
use App\Shared\Domain\DTOs\PaginationParams;
use App\Shared\Domain\DTOs\PaginatedResult;

class InMemoryVocabularyRepository implements VocabularyRepositoryInterface
{
    private array $vocabularies = [];

    public function __construct()
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $dataFile = __DIR__ . '/../Data/terms.php';

        if (file_exists($dataFile)) {
            $data = require $dataFile;

            // Load vocabularies
            foreach ($data['vocabularies'] as $vocabularyData) {
                $vocabulary = new Vocabulary(
                    $vocabularyData['id'],
                    $vocabularyData['name'],
                    $vocabularyData['slug'],
                    $vocabularyData['description'] ?? null,
                    $vocabularyData['workspace_id'] ?? null
                );
                $this->vocabularies[$vocabulary->getId()] = $vocabulary;
            }
        }
    }

    public function save(Vocabulary $vocabulary): void
    {
        $this->vocabularies[$vocabulary->getId()] = $vocabulary;
    }

    public function findById(string $id): ?Vocabulary
    {
        return $this->vocabularies[$id] ?? null;
    }

    public function findAll(PaginationParams $params): PaginatedResult
    {
        $allVocabularies = array_values($this->vocabularies);
        $total = count($allVocabularies);

        // Simple pagination
        $offset = ($params->page - 1) * $params->perPage;
        $paginatedVocabularies = array_slice($allVocabularies, $offset, $params->perPage);
        $lastPage = ceil($total / $params->perPage);

        return new PaginatedResult(
            data: $paginatedVocabularies,
            total: $total,
            page: $params->page,
            perPage: $params->perPage,
            lastPage: $lastPage
        );
    }

    public function existsBySlugAndWorkspace(string $slug, ?string $workspaceId): bool
    {
        foreach ($this->vocabularies as $vocabulary) {
            if ($vocabulary->getSlug() === $slug && $vocabulary->getWorkspaceId() === $workspaceId) {
                return true;
            }
        }
        return false;
    }

    public function delete(Vocabulary $vocabulary): void
    {
        unset($this->vocabularies[$vocabulary->getId()]);
    }
}