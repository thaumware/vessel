<?php

namespace App\Stock\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockMovementsTest extends TestCase
{
    use RefreshDatabase;

    private function loadCSV(string $filename): array
    {
        $path = base_path("app/Stock/Tests/Fixtures/{$filename}");
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        return array_map(function ($row) use ($header) {
            $data = array_combine($header, $row);
            return array_map(fn ($value) => $value === '' ? null : $value, $data);
        }, $rows);
    }

    private function loadFixtures(): void
    {
        $products = $this->loadCSV('products.csv');
        foreach ($products as $product) {
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
                'item_type' => 'unit',
                'item_id' => '018db9d3-7a82-7001-9000-' . str_pad(strval(crc32($product['sku'])), 12, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $movements = $this->loadCSV('stock_movements.csv');
        foreach ($movements as $movement) {
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
                'user_id' => null,
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

        $this->assertGreaterThan(0, $receipts->count(), 'Receipts should exist');

        foreach ($receipts as $receipt) {
            $meta = json_decode($receipt->meta, true);
            $this->assertGreaterThan(0, $receipt->quantity, 'Receipt quantity should be positive');
            $this->assertEquals('supplier', $meta['from_location_code'], 'Receipts should originate from supplier');
        }
    }

    public function test_sale_movements_decrease_stock(): void
    {
        $this->loadFixtures();

        $sales = DB::table('stock_movements')
            ->where('movement_type', 'sale')
            ->get();

        $this->assertGreaterThan(0, $sales->count(), 'Sales should exist');

        foreach ($sales as $sale) {
            $meta = json_decode($sale->meta, true);
            $this->assertLessThan(0, $sale->quantity, 'Sale quantity should be negative');
            $this->assertEquals('customer', $meta['to_location_code'], 'Sales should go to customer');
        }
    }

    public function test_transfer_movements_between_locations(): void
    {
        $this->loadFixtures();

        $transfers = DB::table('stock_movements')
            ->where('movement_type', 'transfer')
            ->get();

        $this->assertGreaterThan(0, $transfers->count(), 'Transfers should exist');

        foreach ($transfers as $transfer) {
            $meta = json_decode($transfer->meta, true);
            $this->assertNotEquals('supplier', $meta['from_location_code'], 'Transfers should not originate from supplier');
            $this->assertNotEquals('customer', $meta['to_location_code'], 'Transfers should not go directly to customer');
            $this->assertNotEmpty($meta['from_location_code'], 'Transfer must have source location');
            $this->assertNotEmpty($meta['to_location_code'], 'Transfer must have destination location');
        }
    }

    public function test_adjustment_movements_with_reason(): void
    {
        $this->loadFixtures();

        $adjustments = DB::table('stock_movements')
            ->where('movement_type', 'adjustment')
            ->get();

        foreach ($adjustments as $adjustment) {
            $this->assertNotEmpty($adjustment->reference, 'Adjustment should include a reason');
            $this->assertLessThan(0, $adjustment->quantity, 'Adjustment quantities are expected to be negative');
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
            $this->assertStringContainsString('QUAR', $meta['to_location_code'], 'Quarantine should go to QUAR location');
            $this->assertNotEmpty($quarantine->reference, 'Quarantine should include a reason');
        }
    }

    public function test_workspace_isolation_in_movements(): void
    {
        $this->loadFixtures();

        $workspaceId = '12345678-1234-1234-1234-123456789012';

        $workspaceMovements = DB::table('stock_movements')
            ->where('workspace_id', $workspaceId)
            ->get();

        $this->assertGreaterThan(0, $workspaceMovements->count(), 'Workspace-specific movements should exist');

        foreach ($workspaceMovements as $movement) {
            $item = DB::table('stock_items')
                ->where('sku', $movement->sku)
                ->where('workspace_id', $workspaceId)
                ->first();

            $this->assertNotNull($item, 'Movement item should belong to the same workspace');
        }
    }

    public function test_global_products_accessible_in_movements(): void
    {
        $this->loadFixtures();

        $globalMovements = DB::table('stock_movements')
            ->whereNull('workspace_id')
            ->get();

        $this->assertGreaterThan(0, $globalMovements->count(), 'Global movements should exist');

        foreach ($globalMovements as $movement) {
            $item = DB::table('stock_items')
                ->where('sku', $movement->sku)
                ->whereNull('workspace_id')
                ->first();

            $this->assertNotNull($item, 'Global movement item should exist');
        }
    }

    public function test_movement_timeline_chronological(): void
    {
        $this->loadFixtures();

        $movements = DB::table('stock_movements')
            ->where('sku', 'ELEC-LAP-001')
            ->orderBy('created_at')
            ->get();

        $this->assertGreaterThan(1, $movements->count(), 'Laptop movements should include a timeline');

        $previousTime = null;
        foreach ($movements as $movement) {
            if ($previousTime) {
                $this->assertGreaterThanOrEqual(
                    strtotime($previousTime),
                    strtotime($movement->created_at),
                    'Movements should be ordered chronologically'
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
                $this->assertStringContainsString('COLD', $meta['to_location_code'], 'Dairy should move through cold storage');
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
            $this->assertStringContainsString('QA', $meta['to_location_code'], 'Pharmaceutical receipts should route to QA first');
        }

        $qaTransfers = DB::table('stock_movements')
            ->where('sku', 'PHAR-MED-201')
            ->where('movement_type', 'transfer')
            ->get()
            ->filter(function ($movement) {
                $meta = json_decode($movement->meta, true);
                return str_starts_with($meta['from_location_code'] ?? '', 'QA-');
            });

        $this->assertGreaterThan(0, $qaTransfers->count(), 'Transfers should leave QA after approval');
    }
}