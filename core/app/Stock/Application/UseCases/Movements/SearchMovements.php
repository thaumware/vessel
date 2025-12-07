<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\Movements;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\ValueObjects\MovementSearchCriteria;

/**
 * Caso de uso: Buscar movimientos con criterios.
 * 
 * Usa una única query al repositorio con todos los filtros.
 */
final class SearchMovements
{
    public function __construct(
        private MovementRepositoryInterface $repository
    ) {
    }

    /**
     * @return array{data: Movement[], total: int, offset: int, limit: ?int}
     */
    public function execute(MovementSearchCriteria $criteria): array
    {
        return [
            'data' => $this->repository->search($criteria),
            'total' => $this->repository->count($criteria),
            'offset' => $criteria->offset,
            'limit' => $criteria->limit,
        ];
    }
}
