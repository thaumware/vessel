<?php

namespace App\Taxonomy\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Term
{
    use HasId;
    private string $name;
    private string $slug;
    private ?string $description;
    private string $vocabularyId;
    private ?string $workspaceId;

    public function __construct(
        string $id,
        string $name,
        string $slug,
        string $vocabularyId,
        ?string $description = null,
        ?string $workspaceId = null,
    ) {
        $this->setId($id);
        $this->name = $name;
        $this->slug = $slug;
        $this->vocabularyId = $vocabularyId;
        $this->description = $description;
        $this->workspaceId = $workspaceId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getVocabularyId(): string
    {
        return $this->vocabularyId;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspaceId;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'description' => $this->getDescription(),
            'vocabulary_id' => $this->getVocabularyId(),
            'workspace_id' => $this->getWorkspaceId(),
        ];
    }
}