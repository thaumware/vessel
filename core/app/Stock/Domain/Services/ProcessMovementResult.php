<?php

declare(strict_types=1);

namespace App\Stock\Domain\Services;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;

/**
 * Resultado de procesar un movimiento.
 */
class ProcessMovementResult
{
    private function __construct(
        private bool $success,
        private Movement $movement,
        private ?StockItem $stockItem = null,
        private ?float $previousBalance = null,
        private array $errors = []
    ) {
    }

    public static function success(
        Movement $movement,
        StockItem $stockItem,
        float $previousBalance
    ): self {
        return new self(
            success: true,
            movement: $movement,
            stockItem: $stockItem,
            previousBalance: $previousBalance
        );
    }

    public static function failure(Movement $movement, array $errors): self
    {
        return new self(
            success: false,
            movement: $movement,
            errors: $errors
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMovement(): Movement
    {
        return $this->movement;
    }

    public function getStockItem(): ?StockItem
    {
        return $this->stockItem;
    }

    public function getPreviousBalance(): ?float
    {
        return $this->previousBalance;
    }

    public function getNewBalance(): ?float
    {
        return $this->stockItem?->getQuantity();
    }

    public function getDelta(): ?float
    {
        if ($this->previousBalance === null || $this->stockItem === null) {
            return null;
        }
        return $this->stockItem->getQuantity() - $this->previousBalance;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'movement' => $this->movement->toArray(),
            'previous_balance' => $this->previousBalance,
            'new_balance' => $this->getNewBalance(),
            'delta' => $this->getDelta(),
            'errors' => $this->errors,
        ];
    }
}
