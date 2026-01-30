<?php
/**
 * Script para crear stock_items a partir de catalog_items
 * Ejecutar: docker compose exec -T core php scripts/seed-stock-from-catalog.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\Uuid;

echo "=== Creando stock_items desde catalog_items ===\n\n";

// Primero, asegurar que existe una ubicación por defecto
$defaultLocation = DB::table('locations_locations')
    ->where('name', 'FabLab Principal')
    ->first();

if (!$defaultLocation) {
    $locationId = Uuid::v4()->toRfc4122();
    $now = now();

    DB::table('locations_locations')->insert([
        'id' => $locationId,
        'name' => 'FabLab Principal',
        'description' => 'Almacén principal del FabLab',
        'type' => 'warehouse',
        'level' => 0,
        'path' => $locationId,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    echo "✓ Ubicación 'FabLab Principal' creada\n\n";
    $defaultLocationId = $locationId;
} else {
    $defaultLocationId = $defaultLocation->id;
    echo "✓ Usando ubicación existente: {$defaultLocation->name}\n\n";
}

// Obtener todos los catalog_items que no tienen stock_item asociado
$catalogItems = DB::table('catalog_items')
    ->whereNull('deleted_at')
    ->get();

echo "Encontrados " . count($catalogItems) . " items en el catálogo\n\n";

$created = 0;
$skipped = 0;
$errors = 0;

foreach ($catalogItems as $catalogItem) {
    // Verificar si ya existe un stock_item para este catalog_item
    $exists = DB::table('stock_items')
        ->where('catalog_item_id', $catalogItem->id)
        ->whereNull('deleted_at')
        ->exists();

    if ($exists) {
        echo "⏭ Ya tiene stock: {$catalogItem->name}\n";
        $skipped++;
        continue;
    }

    try {
        $id = Uuid::v4()->toRfc4122();
        $now = now();

        // Generar SKU basado en el nombre
        $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $catalogItem->name), 0, 10));
        $sku = $sku . '-' . substr($id, 0, 4);

        DB::table('stock_items')->insert([
            'id' => $id,
            'sku' => $sku,
            'catalog_item_id' => $catalogItem->id,
            'catalog_origin' => 'internal_catalog',
            'location_id' => $defaultLocationId,
            'location_type' => 'warehouse',
            'quantity' => 0, // Cantidad inicial 0
            'reserved_quantity' => 0,
            'lot_number' => null,
            'expiration_date' => null,
            'serial_number' => null,
            'workspace_id' => null,
            'meta' => null,
            'created_by_id' => null,
            'created_by_type' => null,
            'status_id' => null,
            'item_type' => 'unit',
            'item_id' => $sku, // Usar SKU como item_id
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        echo "✓ Stock creado: {$catalogItem->name} (SKU: {$sku})\n";
        $created++;
    } catch (\Exception $e) {
        echo "✗ Error en '{$catalogItem->name}': " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Resumen ===\n";
echo "Creados: {$created}\n";
echo "Ya existían: {$skipped}\n";
echo "Errores: {$errors}\n";
echo "Total procesados: " . ($created + $skipped + $errors) . "\n";
