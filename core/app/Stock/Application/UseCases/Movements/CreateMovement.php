<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\Movements;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Services\ProcessMovementResult;
use App\Stock\Domain\Services\StockMovementService;

/**
 * Caso de uso: Crear y procesar un movimiento de stock.
 */
final class CreateMovement
{
    public function __construct(
        private StockMovementService $movementService
    ) {
    }

    public function execute(Movement $movement): ProcessMovementResult
    {
        return $this->movementService->process($movement);
    }
}
