<?php

namespace App\Stock\Tests\Support;

use RuntimeException;

/**
 * PortalStub simula el gateway configurable: carga relaciones por ID desde un JSON externo.
 * - getCatalogItem: retorna taxonomía y metadata de catálogo.
 * - getUom: retorna ratio/precision para conversiones.
 *
 * En productivo, un "portal" podría apuntar a otra API; aquí es un archivo.
 */
class PortalStub
{
    private array $data;

    public function __construct(string $path)
    {
        $json = @file_get_contents($path);
        if ($json === false) {
            throw new RuntimeException("Portal file not found: {$path}");
        }
        $this->data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    public function getCatalogItem(string $itemId): array
    {
        $item = $this->data['items'][$itemId] ?? null;
        if (!$item) {
            throw new RuntimeException("Item not found in portal: {$itemId}");
        }
        return $item;
    }

    public function getUom(string $code): array
    {
        $uom = $this->data['uoms'][$code] ?? null;
        if (!$uom) {
            throw new RuntimeException("UOM not found in portal: {$code}");
        }
        return $uom;
    }
}
