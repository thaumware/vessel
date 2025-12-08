<?php

namespace App\Locations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationsIndependenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_locations_work_without_stock(): void
    {
        $this->loadLocationsFixtures();

        $this->assertTrue(DB::getSchemaBuilder()->hasTable('location_types'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('locations_locations'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('location_rules'));

        $globalTypes = DB::table('location_types')->whereNull('workspace_id')->get();
        $this->assertGreaterThan(0, $globalTypes->count(), 'Global location types should exist');

        $locations = DB::table('locations_locations')->get();
        $this->assertGreaterThan(0, $locations->count());

        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('locations_locations', 'parent_id'));
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('locations_locations', 'level'));
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('locations_locations', 'path'));
    }

    public function test_location_hierarchy_parent_child(): void
    {
        $this->loadLocationsFixtures();

        $warehouse = DB::table('locations_locations')
            ->where('name', 'Bodega Central')
            ->first();

        $this->assertNotNull($warehouse);
        $this->assertEquals(0, $warehouse->level);
        $this->assertNull($warehouse->parent_id);

        $aisle = DB::table('locations_locations')
            ->where('name', 'Pasillo A')
            ->first();

        $this->assertNotNull($aisle);
        $this->assertEquals(1, $aisle->level);
        $this->assertEquals($warehouse->id, $aisle->parent_id);

        $shelf = DB::table('locations_locations')
            ->where('name', 'Estante A1')
            ->first();

        $this->assertNotNull($shelf);
        $this->assertEquals(2, $shelf->level);
        $this->assertEquals($aisle->id, $shelf->parent_id);

        $this->assertStringContainsString($warehouse->id, $shelf->path);
        $this->assertStringContainsString($aisle->id, $shelf->path);
        $this->assertStringContainsString($shelf->id, $shelf->path);
    }

    public function test_location_rules_are_polymorphic(): void
    {
        $this->loadLocationsFixtures();

        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('location_rules', 'locable_type'));
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('location_rules', 'locable_id'));

        $typeRules = DB::table('location_rules')
            ->where('locable_type', 'LocationType')
            ->get();

        $this->assertGreaterThan(0, $typeRules->count(), 'LocationType rules should exist');

        $locationRules = DB::table('location_rules')
            ->where('locable_type', 'Location')
            ->get();

        $this->assertGreaterThan(0, $locationRules->count(), 'Location rules should exist');
    }

    public function test_location_rules_metadata(): void
    {
        $this->loadLocationsFixtures();

        $rule = DB::table('location_rules')
            ->where('rule_key', 'max_capacity')
            ->first();

        $this->assertNotNull($rule);
        $this->assertNotNull($rule->metadata);

        $metadata = json_decode($rule->metadata, true);
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('unit', $metadata);
    }

    public function test_location_types_can_be_global(): void
    {
        $this->loadLocationsFixtures();

        $coldStorage = DB::table('location_types')
            ->where('code', 'cold_storage')
            ->whereNull('workspace_id')
            ->first();

        $this->assertNotNull($coldStorage, 'cold_storage should be global');

        $warehouseType = DB::table('location_types')
            ->where('code', 'warehouse')
            ->first();

        $this->assertNotNull($warehouseType);
    }

    private function loadLocationsFixtures(): void
    {
        $this->loadCSV('location_types.csv', 'location_types');
        $this->loadCSV('locations.csv', 'locations_locations');
        $this->loadCSV('location_rules.csv', 'location_rules');
    }

    private function loadCSV(string $filename, string $table): void
    {
        $path = base_path("app/Locations/Tests/Fixtures/{$filename}");

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

            if (!isset($data['created_at'])) {
                $data['created_at'] = now();
                $data['updated_at'] = now();
            }

            DB::table($table)->insert($data);
        }
    }
}