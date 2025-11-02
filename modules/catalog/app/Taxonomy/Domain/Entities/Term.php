<?php

namespace App\Taxonomy\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class Term
{
    use HasId;
    private string $name;
    private string $vocabulary_id;

    public function __construct(
        string $id,
        string $name,
        string $vocabulary_id
    ) {
        $this->setId($id);

        $this->name = $name;
        $this->vocabulary_id = $vocabulary_id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVocabularyId(): string
    {
        return $this->vocabulary_id;
    }
}