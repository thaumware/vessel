<?php

namespace App\Shared\Domain\DTOs;

class PaginatedResult
{
    public function __construct(
        public readonly array $data,
        public readonly int $total,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $lastPage
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'meta' => [
                'total' => $this->total,
                'page' => $this->page,
                'per_page' => $this->perPage,
                'last_page' => $this->lastPage,
                'from' => ($this->page - 1) * $this->perPage + 1,
                'to' => min($this->page * $this->perPage, $this->total),
            ],
        ];
    }

    public static function fromArray(
        array $data,
        int $total,
        PaginationParams $params
    ): self {
        return new self(
            data: $data,
            total: $total,
            page: $params->page,
            perPage: $params->perPage,
            lastPage: (int) ceil($total / $params->perPage)
        );
    }
}
