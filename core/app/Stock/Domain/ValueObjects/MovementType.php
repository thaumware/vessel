<?php

declare(strict_types=1);

namespace App\Stock\Domain\ValueObjects;

/**
 * Tipo de movimiento de stock.
 * Define la semántica del movimiento y cómo afecta las cantidades.
 */
enum MovementType: string
{
    // === Movimientos que SUMAN stock ===
    case RECEIPT = 'receipt';           // Recepción de mercancía
    case RETURN = 'return';             // Devolución de cliente
    case ADJUSTMENT_IN = 'adjustment_in'; // Ajuste positivo (inventario)
    case TRANSFER_IN = 'transfer_in';   // Transferencia entrante
    case PRODUCTION = 'production';     // Producción/manufactura

    // === Movimientos que RESTAN stock ===
    case SHIPMENT = 'shipment';         // Envío/despacho
    case CONSUMPTION = 'consumption';   // Consumo interno
    case ADJUSTMENT_OUT = 'adjustment_out'; // Ajuste negativo (inventario)
    case TRANSFER_OUT = 'transfer_out'; // Transferencia saliente
    case DAMAGE = 'damage';             // Daño/merma
    case EXPIRATION = 'expiration';     // Vencimiento
    case INSTALLATION = 'installation'; // Instalación (salida por servicio técnico)

    // === Movimientos de RESERVA (no afectan quantity, afectan reserved) ===
    case RESERVE = 'reserve';           // Reservar para orden
    case RELEASE = 'release';           // Liberar reserva

    // === Movimientos NEUTRALES (solo tracking) ===
    case COUNT = 'count';               // Conteo de inventario
    case RELOCATION = 'relocation';     // Reubicación interna (mismo almacén)

    // === Movimiento EXTENSIBLE (custom handlers) ===
    case CUSTOM = 'custom';             // Movimiento personalizado (usa referenceType para identificar)

    /**
     * ¿Este movimiento suma stock?
     */
    public function addsStock(): bool
    {
        return match ($this) {
            self::RECEIPT,
            self::RETURN,
            self::ADJUSTMENT_IN,
            self::TRANSFER_IN,
            self::PRODUCTION => true,
            default => false,
        };
    }

    /**
     * ¿Este movimiento resta stock?
     */
    public function removesStock(): bool
    {
        return match ($this) {
            self::SHIPMENT,
            self::CONSUMPTION,
            self::ADJUSTMENT_OUT,
            self::TRANSFER_OUT,
            self::DAMAGE,
            self::EXPIRATION,
            self::INSTALLATION => true,
            default => false,
        };
    }

    /**
     * ¿Este movimiento afecta reservas?
     */
    public function affectsReservation(): bool
    {
        return match ($this) {
            self::RESERVE,
            self::RELEASE => true,
            default => false,
        };
    }

    /**
     * ¿Reserva stock?
     */
    public function reserves(): bool
    {
        return $this === self::RESERVE;
    }

    /**
     * ¿Libera reserva?
     */
    public function releases(): bool
    {
        return $this === self::RELEASE;
    }

    /**
     * ¿Es movimiento de transferencia?
     */
    public function isTransfer(): bool
    {
        return match ($this) {
            self::TRANSFER_IN,
            self::TRANSFER_OUT => true,
            default => false,
        };
    }

    /**
     * Obtiene el delta a aplicar al stock.
     * Positivo = suma, Negativo = resta, 0 = no afecta quantity
     */
    public function getQuantityMultiplier(): int
    {
        if ($this->addsStock()) {
            return 1;
        }
        if ($this->removesStock()) {
            return -1;
        }
        return 0;
    }

    /**
     * Obtiene el delta a aplicar a reservas.
     */
    public function getReservationMultiplier(): int
    {
        if ($this->reserves()) {
            return 1;
        }
        if ($this->releases()) {
            return -1;
        }
        return 0;
    }

    /**
     * Descripción legible del tipo.
     */
    public function label(): string
    {
        return match ($this) {
            self::RECEIPT => 'Recepción',
            self::RETURN => 'Devolución',
            self::ADJUSTMENT_IN => 'Ajuste entrada',
            self::TRANSFER_IN => 'Transferencia entrada',
            self::PRODUCTION => 'Producción',
            self::SHIPMENT => 'Envío',
            self::CONSUMPTION => 'Consumo',
            self::ADJUSTMENT_OUT => 'Ajuste salida',
            self::TRANSFER_OUT => 'Transferencia salida',
            self::DAMAGE => 'Daño/Merma',
            self::EXPIRATION => 'Vencimiento',
            self::INSTALLATION => 'Instalación',
            self::RESERVE => 'Reserva',
            self::RELEASE => 'Liberación',
            self::COUNT => 'Conteo',
            self::RELOCATION => 'Reubicación',
            self::CUSTOM => 'Personalizado',
        };
    }

    /**
     * Tipos que suman stock.
     * @return self[]
     */
    public static function inboundTypes(): array
    {
        return [
            self::RECEIPT,
            self::RETURN,
            self::ADJUSTMENT_IN,
            self::TRANSFER_IN,
            self::PRODUCTION,
        ];
    }

    /**
     * Tipos que restan stock.
     * @return self[]
     */
    public static function outboundTypes(): array
    {
        return [
            self::SHIPMENT,
            self::CONSUMPTION,
            self::ADJUSTMENT_OUT,
            self::TRANSFER_OUT,
            self::DAMAGE,
            self::EXPIRATION,
            self::INSTALLATION,
        ];
    }
}
