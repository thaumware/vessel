<?php

declare(strict_types=1);

namespace App\Stock\Domain\ValueObjects;

/**
 * Resultado de validacion de capacidad.
 */
final class CapacityValidationResult
{
    private function __construct(
        private bool $isValid,
        private ?string $errorCode = null,
        private ?string $errorMessage = null,
        private ?array $context = null,
    ) {}

    public static function valid(): self
    {
        return new self(true);
    }

    public static function invalid(string $errorCode, string $message, array $context = []): self
    {
        return new self(false, $errorCode, $message, $context);
    }

    public static function exceedsMaxQuantity(float $current, float $requested, float $max, string $locationId): self
    {
        return self::invalid(
            'EXCEEDS_MAX_QUANTITY',
            "Adding {$requested} units would exceed max capacity of {$max} (current: {$current})",
            [
                'current_quantity' => $current,
                'requested_quantity' => $requested,
                'max_quantity' => $max,
                'location_id' => $locationId,
                'would_be_total' => $current + $requested,
            ]
        );
    }

    public static function exceedsMaxWeight(float $current, float $requested, float $max, string $locationId): self
    {
        return self::invalid(
            'EXCEEDS_MAX_WEIGHT',
            "Adding {$requested}kg would exceed max weight of {$max}kg (current: {$current}kg)",
            [
                'current_weight' => $current,
                'requested_weight' => $requested,
                'max_weight' => $max,
                'location_id' => $locationId,
            ]
        );
    }

    public static function itemTypeNotAllowed(string $itemType, array $allowedTypes, string $locationId): self
    {
        return self::invalid(
            'ITEM_TYPE_NOT_ALLOWED',
            "Item type '{$itemType}' is not allowed in this location",
            [
                'item_type' => $itemType,
                'allowed_types' => $allowedTypes,
                'location_id' => $locationId,
            ]
        );
    }

    public static function mixedLotsNotAllowed(string $locationId): self
    {
        return self::invalid(
            'MIXED_LOTS_NOT_ALLOWED',
            'This location does not allow mixing different lots',
            ['location_id' => $locationId]
        );
    }

    public static function mixedItemsNotAllowed(string $locationId): self
    {
        return self::invalid(
            'MIXED_ITEMS_NOT_ALLOWED',
            'This location does not allow mixing different items',
            ['location_id' => $locationId]
        );
    }

    /**
     * @deprecated Use mixedItemsNotAllowed() instead
     */
    public static function mixedSkusNotAllowed(string $locationId): self
    {
        return self::mixedItemsNotAllowed($locationId);
    }

    public static function locationNotActive(string $locationId): self
    {
        return self::invalid(
            'LOCATION_NOT_ACTIVE',
            'Location stock settings are not active',
            ['location_id' => $locationId]
        );
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function isInvalid(): bool
    {
        return !$this->isValid;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'error_code' => $this->errorCode,
            'error_message' => $this->errorMessage,
            'context' => $this->context,
        ];
    }
}
