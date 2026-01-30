<?php
/**
 * Script para corregir los stock items:
 * 1. Cambiar ubicación a "Camptech"
 * 2. Usar el nombre del catálogo en lugar de SKU generado
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Corrigiendo Stock Items ===\n\n";

// 1. Actualizar la ubicación a "Camptech"
echo "1. Actualizando ubicación a 'Camptech'...\n";
$locationUpdated = DB::table('locations_locations')
    ->where('name', 'FabLab Principal')
    ->update(['name' => 'Camptech']);

if ($locationUpdated) {
    echo "   ✓ Ubicación actualizada a 'Camptech'\n";
} else {
    // Verificar si ya existe o crear una nueva
    $location = DB::table('locations_locations')->where('name', 'Camptech')->first();
    if (!$location) {
        $locationId = (string) \Illuminate\Support\Str::uuid();
        DB::table('locations_locations')->insert([
            'id' => $locationId,
            'name' => 'Camptech',
            'type' => 'warehouse',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "   ✓ Ubicación 'Camptech' creada\n";
        
        // Actualizar todos los stock_items para usar esta ubicación
        DB::table('stock_items')->update(['location_id' => $locationId]);
        echo "   ✓ Stock items actualizados con nueva ubicación\n";
    } else {
        echo "   ✓ Ubicación 'Camptech' ya existe\n";
    }
}

// 2. Actualizar stock_items para usar el nombre del catálogo
echo "\n2. Actualizando nombres en stock_items...\n";

// Obtener todos los items del catálogo
$catalogItems = DB::table('catalog_items')
    ->whereNull('deleted_at')
    ->get();

$updated = 0;
foreach ($catalogItems as $catalogItem) {
    // Generar un SKU limpio basado en el nombre
    $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $catalogItem->name);
    $sku = strtoupper(substr($cleanName, 0, 10));
    
    // Actualizar el stock_item correspondiente
    $result = DB::table('stock_items')
        ->where('catalog_item_id', $catalogItem->id)
        ->update([
            'item_id' => $catalogItem->name,
            'sku' => $catalogItem->name,
        ]);
    
    if ($result) {
        $updated++;
    }
}

echo "   ✓ Actualizados {$updated} stock items con nombres del catálogo\n";

// 3. Verificar resultados
echo "\n3. Verificando resultados...\n";

$location = DB::table('locations_locations')->first();
echo "   Ubicación: {$location->name}\n";

$stockItems = DB::table('stock_items')->limit(5)->get();
echo "   Primeros 5 stock items:\n";
foreach ($stockItems as $item) {
    echo "     - SKU: {$item->sku}\n";
}

echo "\n=== Correcciones completadas ===\n";
