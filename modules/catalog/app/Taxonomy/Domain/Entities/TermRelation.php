<?php

namespace App\Taxonomy\Domain\Entities;

use App\Shared\Domain\Traits\HasId;

class TermRelation
{
    use HasId;
    private string $from_term_id;
    private string $to_term_id;
    private string $relation_type;
    private int $depth;

    public function __construct(
        string $id,
        string $from_term_id,
        string $to_term_id,
        string $relation_type = 'parent',
        int $depth = 0
    ) {
        $this->setId($id);
        $this->from_term_id = $from_term_id;
        $this->to_term_id = $to_term_id;
        $this->relation_type = $relation_type;
        $this->depth = $depth;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFromTermId(): string
    {
        return $this->from_term_id;
    }

    public function getToTermId(): string
    {
        return $this->to_term_id;
    }

    public function getRelationType(): string
    {
        return $this->relation_type;
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
