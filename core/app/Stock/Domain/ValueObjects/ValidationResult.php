<?php

declare(strict_types=1);

namespace App\Stock\Domain\ValueObjects;

/**
 * Resultado genérico de validación.
 * 
 * Patrón consistente para todas las validaciones del dominio.
 * Inmutable y con API clara.
 */
class ValidationResult
{
    /** @var string[] */
    private array $errors;

    /**
     * @param string[] $errors
     */
    private function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    /**
     * Crea un resultado válido (sin errores).
     */
    public static function valid(): static
    {
        return new static([]);
    }

    /**
     * Crea un resultado con un error.
     */
    public static function withError(string $error): static
    {
        return new static([$error]);
    }

    /**
     * Crea un resultado con múltiples errores.
     * 
     * @param string[] $errors
     */
    public static function withErrors(array $errors): static
    {
        return new static($errors);
    }

    /**
     * ¿Es válido? (sin errores)
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * ¿Es inválido? (tiene errores)
     */
    public function isInvalid(): bool
    {
        return !$this->isValid();
    }

    /**
     * Obtiene todos los errores.
     * 
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtiene el primer error (útil cuando solo hay uno).
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Combina con otro resultado de validación.
     */
    public function merge(ValidationResult $other): static
    {
        return new static(array_merge($this->errors, $other->errors));
    }

    /**
     * Agrega un error al resultado.
     */
    public function addError(string $error): static
    {
        $errors = $this->errors;
        $errors[] = $error;
        return new static($errors);
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'errors' => $this->errors,
        ];
    }
}
