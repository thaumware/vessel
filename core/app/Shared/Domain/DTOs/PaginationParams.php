<?php

namespace App\Shared\Domain\DTOs;

class PaginationParams
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $sortBy = null,
        public readonly string $sortDirection = 'asc'
    ) {
        if ($this->page < 1) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if ($this->perPage < 1 || $this->perPage > 100) {
            throw new \InvalidArgumentException('Per page must be between 1 and 100');
        }

        if (!in_array($this->sortDirection, ['asc', 'desc'])) {
            throw new \InvalidArgumentException('Sort direction must be asc or desc');
        }
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public static function fromRequest(array $params): self
    {
        return new self(
            page: (int) ($params['page'] ?? 1),
            perPage: (int) ($params['per_page'] ?? 15),
            sortBy: $params['sort_by'] ?? null,
            sortDirection: $params['sort_direction'] ?? 'asc'
        );
    }
}
