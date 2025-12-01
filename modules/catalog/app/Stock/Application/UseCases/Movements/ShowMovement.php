<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\Movements;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;

/**
 * Caso de uso: Obtener un movimiento por ID.
 */
final class ShowMovement
{
    public function __construct(
        private MovementRepositoryInterface $repository
    ) {
    }

    public function execute(string $id): ?Movement
    {
        return $this->repository->findById($id);
    }
}
