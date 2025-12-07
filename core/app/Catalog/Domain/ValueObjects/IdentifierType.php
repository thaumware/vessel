<?php

namespace App\Catalog\Domain\ValueObjects;

/**
 * Tipos de identificadores soportados
 */
enum IdentifierType: string
{
    case SKU = 'sku';                    // SKU interno
    case EAN = 'ean';                    // European Article Number (código de barras)
    case UPC = 'upc';                    // Universal Product Code
    case ISBN = 'isbn';                  // Para libros
    case SUPPLIER = 'supplier';          // Código del proveedor
    case MANUFACTURER = 'manufacturer';  // Número de parte del fabricante
    case HS_CODE = 'hs_code';           // Código aduanero armonizado
    case CUSTOM = 'custom';              // Cualquier otro código

    public function label(): string
    {
        return match ($this) {
            self::SKU => 'SKU',
            self::EAN => 'EAN-13',
            self::UPC => 'UPC-A',
            self::ISBN => 'ISBN',
            self::SUPPLIER => 'Código Proveedor',
            self::MANUFACTURER => 'Número de Parte',
            self::HS_CODE => 'Código Arancelario',
            self::CUSTOM => 'Personalizado',
        };
    }
}
