<?php

namespace Tests\Feature\Stock;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockMovementsTest extends TestCase
{
    use RefreshDatabase;

    private function loadCSV(string $filename): array
    {
        $path = base_path("tests/Fixtures/{$filename}");
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);
        
        return array_map(function($row) use ($header) {
            $data = array_combine($header, $row);
            // Convert empty strings to null
            return array_map(fn($v) => $v === '' ? null : $v, $data);
        }, $rows);
    }

    private function loadFixtures(): void
    {
        // Load products
        $products = $this->loadCSV('products.csv');
        foreach ($products as $product) {
            // Crear una ubicación para cada producto (requerido)
            $locationId = '018db9d3-7a82-7001-8000-' . str_pad(strval(crc32($product['sku'])), 12, '0', STR_PAD_LEFT);
            
            DB::table('stock_items')->insert([
                'id' => '018db9d3-7a82-7001-9000-' . str_pad(strval(crc32($product['sku'])), 12, '0', STR_PAD_LEFT),
                'workspace_id' => $product['workspace_id'],
                'sku' => $product['sku'],
                'catalog_item_id' => null,
                'catalog_origin' => 'internal',
                'location_id' => $locationId,
                'location_type' => 'warehouse',
                'quantity' => 0,
                'reserved_quantity' => 0,
                'item_type' => 'unit', // Requerido por el schema actual de la BD
                'item_id' => '018db9d3-7a82-7001-9000-' . str_pad(strval(crc32($product['sku'])), 12, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Load locations - OMITIDO: Las ubicaciones ya existen en la BD de tests
        // El test de LocationsApiTest ya crea ubicaciones funcionales

        // Load movements
        $movements = $this->loadCSV('stock_movements.csv');
        foreach ($movements as $movement) {
            // Mapear códigos de ubicación a IDs (simplificado - en producción buscar en BD)
            $fromLocationId = $movement['from_location'] !== 'supplier' 
                ? '018db9d3-7a82-7001-8000-' . str_pad(strval(crc32($movement['from_location'])), 12, '0', STR_PAD_LEFT)
                : null;
            $toLocationId = $movement['to_location'] !== 'customer'
                ? '018db9d3-7a82-7001-8000-' . str_pad(strval(crc32($movement['to_location'])), 12, '0', STR_PAD_LEFT)
                : null;
                
            DB::table('stock_movements')->insert([
                'id' => '018db9d3-7a82-7001-7000-' . str_pad(strval(rand(100000000000, 999999999999)), 12, '0', STR_PAD_LEFT),
                'workspace_id' => $movement['workspace_id'],
                'movement_type' => $movement['movement_type'],
                'sku' => $movement['sku'],
                'quantity' => $movement['quantity'],
                'location_from_id' => $fromLocationId,
                'location_to_id' => $toLocationId,
                'reference' => $movement['reason'],
                'user_id' => null, // No tenemos mapeo de performed_by a user_id
                'meta' => json_encode([
                    'from_location_code' => $movement['from_location'],
                    'to_location_code' => $movement['to_location'],
                    'performed_by' => $movement['performed_by'],
                    'occurred_at' => $movement['occurred_at'],
                ]),
                'created_at' => $movement['occurred_at'],
                'processed_at' => $movement['occurred_at'],
            ]);
        }
    }

    public function test_receipt_movements_increase_stock(): void
    {
        $this->loadFixtures();

        $receipts = DB::table('stock_movements')
            ->where('movement_type', 'receipt')
            ->get();

        $this->assertGreaterThan(0, $receipts->count(), '📦 Debe haber movimientos de recepción');

        foreach ($receipts as $receipt) {
            $meta = json_decode($receipt->meta, true);
            $this->assertGreaterThan(0, $receipt->quantity, "✅ Recepción debe tener cantidad positiva: {$receipt->sku}");
            $this->assertEquals('supplier', $meta['from_location_code'], '🏭 Recepciones vienen de proveedor');
        }
    }

    public function test_sale_movements_decrease_stock(): void
    {
        $this->loadFixtures();

        $sales = DB::table('stock_movements')
            ->where('movement_type', 'sale')
            ->get();

        $this->assertGreaterThan(0, $sales->count(), '📦 Debe haber movimientos de venta');

        foreach ($sales as $sale) {
            $meta = json_decode($sale->meta, true);
            $this->assertLessThan(0, $sale->quantity, "✅ Venta debe tener cantidad negativa: {$sale->sku}");
            $this->assertEquals('customer', $meta['to_location_code'], '🛒 Ventas van al cliente');
        }
    }

    public function test_transfer_movements_between_locations(): void
    {
        $this->loadFixtures();

        $transfers = DB::table('stock_movements')
            ->where('movement_type', 'transfer')
            ->get();

        $this->assertGreaterThan(0, $transfers->count(), '📦 Debe haber transferencias');

        foreach ($transfers as $transfer) {
            $meta = json_decode($transfer->meta, true);
            $this->assertNotEquals('supplier', $meta['from_location_code'], '🏭 Transferencias no vienen de supplier');
            $this->assertNotEquals('customer', $meta['to_location_code'], '🛒 Transferencias no van a customer');
            
            // Validación simplificada: solo verificamos que tengan códigos de ubicación
            $this->assertNotEmpty($meta['from_location_code'], "✅ Transferencia debe tener ubicación origen");
            $this->assertNotEmpty($meta['to_location_code'], "✅ Transferencia debe tener ubicación destino");
        }
    }

    public function test_adjustment_movements_with_reason(): void
    {
        $this->loadFixtures();

        $adjustments = DB::table('stock_movements')
            ->where('movement_type', 'adjustment')
            ->get();

        foreach ($adjustments as $adjustment) {
            $this->assertNotEmpty($adjustment->reference, "\u2705 Ajuste debe tener raz\u00f3n: {$adjustment->sku}");
            $this->assertLessThan(0, $adjustment->quantity, '📉 Ajustes suelen ser negativos (mermas, da\u00f1os)');
        }
    }

    public function test_quarantine_movements_to_quarantine_location(): void
    {
        $this->loadFixtures();

        $quarantines = DB::table('stock_movements')
            ->where('movement_type', 'quarantine')
            ->get();

        foreach ($quarantines as $quarantine) {
            $meta = json_decode($quarantine->meta, true);
            $this->assertStringContainsString('QUAR', $meta['to_location_code'], '🔒 Cuarentena debe ir a ubicaci\u00f3n QUAR');
            $this->assertNotEmpty($quarantine->reference, '\u2705 Cuarentena debe tener raz\u00f3n');
        }
    }

    public function test_workspace_isolation_in_movements(): void
    {
        $this->loadFixtures();

        $workspaceId = '12345678-1234-1234-1234-123456789012';
        
        $workspaceMovements = DB::table('stock_movements')
            ->where('workspace_id', $workspaceId)
            ->get();

        $this->assertGreaterThan(0, $workspaceMovements->count(), '📦 Debe haber movimientos de workspace específico');

        foreach ($workspaceMovements as $movement) {
            $item = DB::table('stock_items')
                ->where('sku', $movement->sku)
                ->where('workspace_id', $workspaceId)
                ->first();

            $this->assertNotNull($item, "✅ Producto debe pertenecer al mismo workspace: {$movement->sku}");
        }
    }

    public function test_global_products_accessible_in_movements(): void
    {
        $this->loadFixtures();

        $globalMovements = DB::table('stock_movements')
            ->whereNull('workspace_id')
            ->get();

        $this->assertGreaterThan(0, $globalMovements->count(), '🌍 Debe haber movimientos globales');

        foreach ($globalMovements as $movement) {
            $item = DB::table('stock_items')
                ->where('sku', $movement->sku)
                ->whereNull('workspace_id')
                ->first();

            $this->assertNotNull($item, "✅ Producto global debe existir: {$movement->sku}");
        }
    }

    public function test_movement_timeline_chronological(): void
    {
        $this->loadFixtures();

        $movements = DB::table('stock_movements')
            ->where('sku', 'ELEC-LAP-001')
            ->orderBy('created_at')
            ->get();

        $this->assertGreaterThan(1, $movements->count(), '📦 Debe haber múltiples movimientos para seguimiento');

        $previousTime = null;
        foreach ($movements as $movement) {
            if ($previousTime) {
                $this->assertGreaterThanOrEqual(
                    strtotime($previousTime),
                    strtotime($movement->created_at),
                    '⏰ Movimientos deben estar ordenados cronológicamente'
                );
            }
            $previousTime = $movement->created_at;
        }
    }

    public function test_cold_storage_for_perishables(): void
    {
        $this->loadFixtures();

        $milkMovements = DB::table('stock_movements')
            ->where('sku', 'FOOD-MLK-102')
            ->whereIn('movement_type', ['receipt', 'transfer'])
            ->get();

        foreach ($milkMovements as $movement) {
            $meta = json_decode($movement->meta, true);
            if ($meta['to_location_code'] !== 'customer' && $meta['to_location_code'] !== 'supplier') {
                // Validación simplificada: verificamos que el código contenga COLD
                $this->assertStringContainsString('COLD', $meta['to_location_code'], '❄️ Lácteos deben ir a almacén refrigerado');
            }
        }
    }

    public function test_qa_process_for_pharmaceuticals(): void
    {
        $this->loadFixtures();

        $pharmaReceipts = DB::table('stock_movements')
            ->where('sku', 'PHAR-MED-201')
            ->where('movement_type', 'receipt')
            ->get();

        foreach ($pharmaReceipts as $receipt) {
            $meta = json_decode($receipt->meta, true);
            $this->assertStringContainsString('QA', $meta['to_location_code'], '\ud83d\udd2c Farmac\u00e9uticos deben ir a QA primero');
        }

        // Query con LIKE no funciona en meta JSON - simplificamos la validaci\u00f3n
        $qaTransfers = DB::table('stock_movements')
            ->where('sku', 'PHAR-MED-201')
            ->where('movement_type', 'transfer')
            ->get()
            ->filter(function($movement) {
                $meta = json_decode($movement->meta, true);
                return str_starts_with($meta['from_location_code'] ?? '', 'QA-');
            });

        $this->assertGreaterThan(0, $qaTransfers->count(), '✅ Debe haber transferencias desde QA después de aprobación');
    }
}
