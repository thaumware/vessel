<?php

namespace App\Taxonomy\Domain\DTOs;

class TermTreeNode
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly int $depth,
        public readonly array $children = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'depth' => $this->depth,
            'children' => array_map(fn(TermTreeNode $child) => $child->toArray(), $this->children),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            depth: $data['depth'] ?? 0,
            children: array_map(
                fn($child) => self::fromArray($child),
                $data['children'] ?? []
            )
        );
    }
}
