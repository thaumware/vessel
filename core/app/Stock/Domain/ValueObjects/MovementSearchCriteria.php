<?php

declare(strict_types=1);

namespace App\Stock\Domain\ValueObjects;

use DateTimeInterface;

/**
 * Criterios de búsqueda para movimientos.
 * 
 * Encapsula todos los filtros posibles para que el repositorio
 * pueda construir una única query optimizada.
 */
final class MovementSearchCriteria
{
    public function __construct(
        public readonly ?string $itemId = null,
        public readonly ?string $locationId = null,
        public readonly ?MovementType $type = null,
        public readonly ?MovementStatus $status = null,
        public readonly ?string $lotId = null,
        public readonly ?string $referenceType = null,
        public readonly ?string $referenceId = null,
        public readonly ?DateTimeInterface $dateFrom = null,
        public readonly ?DateTimeInterface $dateTo = null,
        public readonly ?string $workspaceId = null,
        public readonly int $offset = 0,
        public readonly ?int $limit = null,
        public readonly string $sortBy = 'created_at',
        public readonly bool $sortDesc = true,
    ) {
    }

    /**
     * Crea criterios desde array (útil para requests HTTP).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            itemId: $data['item_id'] ?? $data['sku'] ?? null,
            locationId: $data['location_id'] ?? null,
            type: isset($data['type']) ? MovementType::tryFrom($data['type']) : null,
            status: isset($data['status']) ? MovementStatus::tryFrom($data['status']) : null,
            lotId: $data['lot_id'] ?? $data['lot_number'] ?? null,
            referenceType: $data['reference_type'] ?? null,
            referenceId: $data['reference_id'] ?? null,
            dateFrom: isset($data['date_from']) ? new \DateTimeImmutable($data['date_from']) : null,
            dateTo: isset($data['date_to']) ? new \DateTimeImmutable($data['date_to']) : null,
            workspaceId: $data['workspace_id'] ?? null,
            offset: (int) ($data['offset'] ?? 0),
            limit: isset($data['limit']) ? (int) $data['limit'] : null,
            sortBy: $data['sort_by'] ?? 'created_at',
            sortDesc: ($data['sort'] ?? 'desc') === 'desc',
        );
    }

    public function hasFilters(): bool
    {
        return $this->itemId !== null
            || $this->locationId !== null
            || $this->type !== null
            || $this->status !== null
            || $this->lotId !== null
            || $this->referenceType !== null
            || $this->referenceId !== null
            || $this->dateFrom !== null
            || $this->workspaceId !== null;
    }
}
