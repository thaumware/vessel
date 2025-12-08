<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Services;

use App\Stock\Domain\Interfaces\MovementHandlerInterface;

/**
 * Registro de handlers personalizados para tipos de movimiento extendidos.
 * 
 * Permite a la infraestructura agregar nuevos tipos sin modificar el Domain.
 * 
 * Uso desde ServiceProvider:
 * 
 * ```php
 * $registry = $this->app->make(MovementHandlerRegistry::class);
 * $registry->register(new CustomerLoanHandler());
 * $registry->register(new RepairInHandler());
 * $registry->register(new ConsignmentHandler());
 * ```
 */
class MovementHandlerRegistry
{
    /** @var MovementHandlerInterface[] */
    private array $handlers = [];

    /**
     * Registra un handler personalizado.
     */
    public function register(MovementHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * Encuentra el handler para un tipo de movimiento.
     * 
     * @return MovementHandlerInterface|null null si no hay handler custom (usar lógica estándar del enum)
     */
    public function findHandler(string $movementType): ?MovementHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($movementType)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * ¿Hay un handler registrado para este tipo?
     */
    public function hasHandler(string $movementType): bool
    {
        return $this->findHandler($movementType) !== null;
    }

    /**
     * Obtiene todos los handlers registrados.
     * 
     * @return MovementHandlerInterface[]
     */
    public function all(): array
    {
        return $this->handlers;
    }

    /**
     * Cantidad de handlers registrados.
     */
    public function count(): int
    {
        return count($this->handlers);
    }
}
