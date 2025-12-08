<?php

namespace App\Stock\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockStatusIndependenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_statuses_work_without_locations(): void
    {
        $this->loadStockFixtures();

        $this->assertTrue(DB::getSchemaBuilder()->hasTable('stock_statuses'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('stock_status_rules'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('stock_status_transitions'));

        $statuses = DB::table('stock_statuses')->count();
        $this->assertGreaterThan(0, $statuses, 'Stock statuses should be seeded');

        $globalStatuses = DB::table('stock_statuses')->whereNull('workspace_id')->get();
        $this->assertGreaterThan(0, $globalStatuses->count(), 'Global stock statuses should exist');

        $availableStatus = DB::table('stock_statuses')->where('code', 'available')->first();
        $this->assertNotNull($availableStatus);

        $rules = DB::table('stock_status_rules')->where('status_id', $availableStatus->id)->get();
        $this->assertGreaterThan(0, $rules->count(), 'Available status should have rules');

        $allowMovements = $rules->firstWhere('rule_type', 'allow_movements');
        $this->assertNotNull($allowMovements);
        $this->assertTrue((bool) $allowMovements->rule_value, 'Available status should allow movements');

        $transitions = DB::table('stock_status_transitions')
            ->where('from_status_id', $availableStatus->id)
            ->get();
        $this->assertGreaterThan(0, $transitions->count(), 'Available status should have transitions');
    }

    public function test_stock_events_table_exists_and_accepts_inserts(): void
    {
        $this->loadStockFixtures();

        $this->assertTrue(DB::getSchemaBuilder()->hasTable('stock_status_events'));

        $statusId = DB::table('stock_statuses')->first()->id;

        DB::table('stock_status_events')->insert([
            'id' => '018db9d3-7a82-7001-8000-600000000001',
            'status_id' => $statusId,
            'event_type' => 'status_created',
            'metadata' => json_encode(['source' => 'test']),
            'triggered_by' => null,
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $event = DB::table('stock_status_events')->first();
        $this->assertNotNull($event);
        $this->assertEquals('status_created', $event->event_type);
    }

    public function test_stock_status_history_tracks_changes(): void
    {
        $this->loadStockFixtures();

        $this->assertTrue(DB::getSchemaBuilder()->hasTable('stock_status_history'));

        $itemId = '018db9d3-7a82-7001-8000-700000000001';
        $availableStatus = DB::table('stock_statuses')->where('code', 'available')->first();
        $reservedStatus = DB::table('stock_statuses')->where('code', 'reserved')->first();

        DB::table('stock_items')->insert([
            'id' => $itemId,
            'sku' => 'TEST-SKU-001',
            'catalog_item_id' => '018db9d3-7a82-7001-8000-999999999999',
            'location_id' => '018db9d3-7a82-7001-8000-999999999999',
            'quantity' => 100,
            'reserved_quantity' => 0,
            'status_id' => $availableStatus->id,
            'item_type' => 'unit',
            'item_id' => '018db9d3-7a82-7001-8000-999999999999',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_status_history')->insert([
            'id' => '018db9d3-7a82-7001-8000-800000000001',
            'stock_item_id' => $itemId,
            'from_status_id' => $availableStatus->id,
            'to_status_id' => $reservedStatus->id,
            'movement_id' => null,
            'reason' => 'Added to cart',
            'metadata' => json_encode(['cart_id' => 'ABC123']),
            'changed_by' => null,
            'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $history = DB::table('stock_status_history')->where('stock_item_id', $itemId)->first();
        $this->assertNotNull($history);
        $this->assertEquals($reservedStatus->id, $history->to_status_id);
        $this->assertEquals('Added to cart', $history->reason);
    }

    public function test_stock_items_polymorphic_structure(): void
    {
        $this->loadStockFixtures();

        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('stock_items', 'item_type'));
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('stock_items', 'item_id'));

        $types = ['lot', 'unit', 'batch'];

        foreach ($types as $index => $type) {
            DB::table('stock_items')->insert([
                'id' => "018db9d3-7a82-7001-8000-90000000000{$index}",
                'sku' => "TEST-SKU-00{$index}",
                'catalog_item_id' => '018db9d3-7a82-7001-8000-999999999999',
                'location_id' => '018db9d3-7a82-7001-8000-999999999999',
                'quantity' => 10,
                'reserved_quantity' => 0,
                'status_id' => DB::table('stock_statuses')->first()->id,
                'item_type' => $type,
                'item_id' => "018db9d3-7a82-7001-8000-99999999999{$index}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $items = DB::table('stock_items')->whereIn('item_type', $types)->get();
        $this->assertEquals(count($types), $items->count());
    }

    private function loadStockFixtures(): void
    {
        $this->loadCSV('stock_statuses.csv', 'stock_statuses');
        $this->loadCSV('stock_status_rules.csv', 'stock_status_rules');
        $this->loadCSV('stock_status_transitions.csv', 'stock_status_transitions');
    }

    private function loadCSV(string $filename, string $table): void
    {
        $path = base_path("app/Stock/Tests/Fixtures/{$filename}");

        if (!file_exists($path)) {
            $this->fail("Fixture file not found: {$filename}");
        }

        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            DB::table($table)->insert($data);
        }
    }
}