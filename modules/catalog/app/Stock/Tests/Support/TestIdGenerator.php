<?php

declare(strict_types=1);

namespace App\Stock\Tests\Support;

use App\Shared\Domain\Interfaces\IdGeneratorInterface;

/**
 * Generador de IDs para testing.
 * 
 * Permite:
 * - IDs predecibles en secuencia
 * - IDs específicos con setNextId()
 */
class TestIdGenerator implements IdGeneratorInterface
{
    private int $counter = 0;
    private ?string $nextId = null;
    private string $prefix = 'test-';

    public function generate(): string
    {
        if ($this->nextId !== null) {
            $id = $this->nextId;
            $this->nextId = null;
            return $id;
        }

        return $this->prefix . ++$this->counter;
    }

    /**
     * Establece el próximo ID a generar.
     */
    public function setNextId(string $id): self
    {
        $this->nextId = $id;
        return $this;
    }

    /**
     * Establece el prefijo para IDs generados.
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Reinicia el contador.
     */
    public function reset(): self
    {
        $this->counter = 0;
        $this->nextId = null;
        return $this;
    }

    /**
     * Obtiene el contador actual.
     */
    public function getCounter(): int
    {
        return $this->counter;
    }
}
