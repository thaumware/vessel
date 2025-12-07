<?php

namespace App\Shared\Domain\DTOs;

/**
 * FilterParams - Parámetros estándar para filtrado y paginación
 * 
 * Uso:
 *   $params = FilterParams::fromRequest($request->query());
 *   $result = $repository->search($params);
 */
class FilterParams
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $sortBy = null,
        public readonly string $sortDirection = 'asc',
        public readonly array $filters = [],
        public readonly ?string $search = null,
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

    public function getFilter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }

    public function hasFilter(string $key): bool
    {
        return isset($this->filters[$key]);
    }

    /**
     * Crear desde query params de request
     * 
     * Query params reservados:
     *   - page, per_page: paginación
     *   - sort_by, sort_direction: ordenamiento
     *   - search: búsqueda general
     * 
     * Todo lo demás se considera un filtro
     */
    public static function fromRequest(array $params): self
    {
        $reserved = ['page', 'per_page', 'sort_by', 'sort_direction', 'search'];
        
        $filters = array_filter(
            $params,
            fn($key) => !in_array($key, $reserved),
            ARRAY_FILTER_USE_KEY
        );

        // Convertir 'true'/'false' strings a booleans
        foreach ($filters as $key => $value) {
            if ($value === 'true') $filters[$key] = true;
            if ($value === 'false') $filters[$key] = false;
            if ($value === '1') $filters[$key] = true;
            if ($value === '0') $filters[$key] = false;
        }

        return new self(
            page: (int) ($params['page'] ?? 1),
            perPage: (int) ($params['per_page'] ?? 15),
            sortBy: $params['sort_by'] ?? null,
            sortDirection: $params['sort_direction'] ?? 'asc',
            filters: $filters,
            search: $params['search'] ?? null,
        );
    }

    /**
     * Crear PaginationParams compatible (para backwards compatibility)
     */
    public function toPaginationParams(): PaginationParams
    {
        return new PaginationParams(
            page: $this->page,
            perPage: $this->perPage,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
        );
    }
}
