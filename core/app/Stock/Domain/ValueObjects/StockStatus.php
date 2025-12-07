<?php

declare(strict_types=1);

namespace App\Stock\Domain\ValueObjects;

use JsonSerializable;

/**
 * Estado de stock personalizable con reglas de comportamiento.
 * 
 * Los estados se definen en config y pueden tener reglas como:
 * - allow_movements: si permite movimientos de salida
 * - allow_reservations: si se puede reservar desde este estado
 * - blocks_availability: si bloquea disponibilidad (no cuenta como disponible)
 * - requires_approval: si movimientos requieren aprobación
 * - auto_transition: transición automática a otro estado bajo condiciones
 */
final class StockStatus implements JsonSerializable
{
    private function __construct(
        private readonly string $code,
        private readonly string $label,
        private readonly array $rules
    ) {}

    public static function fromConfig(string $code): self
    {
        $statuses = config('stock.statuses', []);
        
        if (!isset($statuses[$code])) {
            throw new \InvalidArgumentException("Stock status '{$code}' not defined in config");
        }

        $config = $statuses[$code];
        
        return new self(
            code: $code,
            label: $config['label'] ?? $code,
            rules: $config['rules'] ?? []
        );
    }

    public static function create(string $code, string $label, array $rules = []): self
    {
        return new self($code, $label, $rules);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function allowsMovements(): bool
    {
        return $this->rules['allow_movements'] ?? true;
    }

    public function allowsReservations(): bool
    {
        return $this->rules['allow_reservations'] ?? true;
    }

    public function blocksAvailability(): bool
    {
        return $this->rules['blocks_availability'] ?? false;
    }

    public function requiresApproval(): bool
    {
        return $this->rules['requires_approval'] ?? false;
    }

    public function getAutoTransition(): ?array
    {
        return $this->rules['auto_transition'] ?? null;
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        $allowedTransitions = $this->rules['allowed_transitions'] ?? null;
        
        // Si no hay restricciones, cualquier transición es válida
        if ($allowedTransitions === null) {
            return true;
        }

        return in_array($targetStatus, $allowedTransitions, true);
    }

    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'rules' => $this->rules,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }
}
