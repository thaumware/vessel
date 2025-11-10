<?php

namespace App\Taxonomy\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Term
{
    use HasId;
    private string $name;
    private string $slug;
    private ?string $description;
    private string $vocabulary_id;
    private ?string $workspace_id;

    public function __construct(
        string $id,
        string $name,
        string $slug,
        string $vocabulary_id,
        ?string $description = null,
        ?string $workspace_id = null,
    ) {
        $this->setId($id);
        $this->name = $name;
        $this->slug = $slug;
        $this->vocabulary_id = $vocabulary_id;
        $this->description = $description;
        $this->workspace_id = $workspace_id;
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
        return $this->vocabulary_id;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspace_id;
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