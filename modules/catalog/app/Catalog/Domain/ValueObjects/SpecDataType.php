<?php

namespace App\Catalog\Domain\ValueObjects;

/**
 * Tipos de datos para especificaciones
 */
enum SpecDataType: string
{
    case String = 'string';
    case Number = 'number';
    case Boolean = 'boolean';
    case Json = 'json';
    case Date = 'date';

    public function cast(mixed $value): mixed
    {
        return match ($this) {
            self::String => (string) $value,
            self::Number => is_numeric($value) ? (float) $value : null,
            self::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            self::Json => is_string($value) ? json_decode($value, true) : $value,
            self::Date => $value,
        };
    }
}
