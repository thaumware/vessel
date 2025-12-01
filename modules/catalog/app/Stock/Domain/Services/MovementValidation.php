<?php

declare(strict_types=1);

namespace App\Stock\Domain\Services;

/**
 * Resultado de validación de movimiento.
 */
class MovementValidation
{
    public function __construct(private array $errors = [])
    {
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'errors' => $this->errors,
        ];
    }
}
