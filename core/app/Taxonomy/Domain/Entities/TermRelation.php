<?php

namespace App\Taxonomy\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class TermRelation
{
    use HasId;
    private string $fromTermId;
    private string $toTermId;
    private string $relationType;
    private int $depth;

    public function __construct(
        string $id,
        string $fromTermId,
        string $toTermId,
        string $relationType = 'parent',
        int $depth = 0
    ) {
        $this->setId($id);
        $this->fromTermId = $fromTermId;
        $this->toTermId = $toTermId;
        $this->relationType = $relationType;
        $this->depth = $depth;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFromTermId(): string
    {
        return $this->fromTermId;
    }

    public function getToTermId(): string
    {
        return $this->toTermId;
    }

    public function getRelationType(): string
    {
        return $this->relationType;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'from_term_id' => $this->getFromTermId(),
            'to_term_id' => $this->getToTermId(),
            'relation_type' => $this->getRelationType(),
            'depth' => $this->getDepth(),
        ];
    }
}
