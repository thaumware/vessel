<?php

namespace App\Stock\Domain\Interfaces;

use App\Stock\Domain\Entities\Movement;

interface MovementRepositoryInterface
{
    public function save(Movement $movement): Movement;

    public function findByMovementId(string $movementId): ?Movement;
}
