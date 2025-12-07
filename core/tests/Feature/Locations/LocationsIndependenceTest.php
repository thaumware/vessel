<?php

namespace Tests\Feature\Locations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationsIndependenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que Locations module funciona SIN Stock module
     */
    public function test_locations_work_independently_without_stock(): void
    {
        // 1. Cargar datos de locations desde CSV
        $this->loadLocationsFixtures();

        // 2. Verificar que las tablas existen
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('location_types'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('locations_locations'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('location_rules'));

        // 3. Verificar location types globales
        $globalTypes = DB::table('location_types')->whereNull('workspace_id')->get();
        $this->assertGreaterThan(0, $globalTypes->count(), 'Debe haber location types globales');

        // 4. Verificar jerarquía
        $locations = DB::table('locations_locations')->get();
        $this->assertGreaterThan(0, $locations->count());

        // Verificar columnas de jerarquía
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('locations_locations', 'parent_id'));
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('locations_locations', 'level'));
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('locations_locations', 'path'));

        echo "\n✅ Locations module funciona independientemente SIN Stock\n";
    }

    /**
     * Test de jerarquía padre/hijo
     */
    public function test_location_hierarchy_parent_child(): void
    {
        $this->loadLocationsFixtures();

        // Buscar bodega (nivel 0)
        $bodega = DB::table('locations_locations')
            ->where('name', 'Bodega Central')
            ->first();
        
        $this->assertNotNull($bodega);
        $this->assertEquals(0, $bodega->level);
        $this->assertNull($bodega->parent_id);

        // Buscar pasillo (nivel 1, hijo de bodega)
        $pasillo = DB::table('locations_locations')
            ->where('name', 'Pasillo A')
            ->first();
        
        $this->assertNotNull($pasillo);
        $this->assertEquals(1, $pasillo->level);
        $this->assertEquals($bodega->id, $pasillo->parent_id);

        // Buscar estante (nivel 2, hijo de pasillo)
        $estante = DB::table('locations_locations')
            ->where('name', 'Estante A1')
            ->first();
        
        $this->assertNotNull($estante);
        $this->assertEquals(2, $estante->level);
        $this->assertEquals($pasillo->id, $estante->parent_id);

        // Verificar path completo
        $this->assertStringContainsString($bodega->id, $estante->path);
        $this->assertStringContainsString($pasillo->id, $estante->path);
        $this->assertStringContainsString($estante->id, $estante->path);

        echo "\n✅ Jerarquía locations (bodega→pasillo→estante) funciona\n";
    }

    /**
     * Test de reglas polimórficas (Location vs LocationType)
     */
    public function test_location_rules_polymorphic(): void
    {
        $this->loadLocationsFixtures();

        // Verificar columnas polimórficas
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('location_rules', 'locable_type'));
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('location_rules', 'locable_id'));

        // Reglas por LocationType (aplica a todas las locations de ese tipo)
        $typeRules = DB::table('location_rules')
            ->where('locable_type', 'LocationType')
            ->get();
        
        $this->assertGreaterThan(0, $typeRules->count(), 'Debe haber reglas por LocationType');

        // Reglas por Location específica
        $locationRules = DB::table('location_rules')
            ->where('locable_type', 'Location')
            ->get();
        
        $this->assertGreaterThan(0, $locationRules->count(), 'Debe haber reglas por Location');

        echo "\n✅ Location rules polimórficas (Location y LocationType) funcionan\n";
    }

    /**
     * Test de reglas con metadata JSON
     */
    public function test_location_rules_metadata(): void
    {
        $this->loadLocationsFixtures();

        // Buscar regla con metadata
        $rule = DB::table('location_rules')
            ->where('rule_key', 'max_capacity')
            ->first();
        
        $this->assertNotNull($rule);
        $this->assertNotNull($rule->metadata);

        $metadata = json_decode($rule->metadata, true);
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('unit', $metadata);

        echo "\n✅ Location rules con metadata JSON funcionan\n";
    }

    /**
     * Test que location types tienen workspace_id null (global)
     */
    public function test_location_types_can_be_global(): void
    {
        $this->loadLocationsFixtures();

        $coldStorage = DB::table('location_types')
            ->where('code', 'cold_storage')
            ->whereNull('workspace_id')
            ->first();
        
        $this->assertNotNull($coldStorage, 'cold_storage debe ser global');

        $warehouseType = DB::table('location_types')
            ->where('code', 'warehouse')
            ->first();
        
        $this->assertNotNull($warehouseType);

        echo "\n✅ Location types pueden ser globales (workspace_id null)\n";
    }

    private function loadLocationsFixtures(): void
    {
        $this->loadCSV('location_types.csv', 'location_types');
        $this->loadCSV('locations.csv', 'locations_locations');
        $this->loadCSV('location_rules.csv', 'location_rules');
    }

    private function loadCSV(string $filename, string $table): void
    {
        $path = base_path("tests/Fixtures/{$filename}");
        
        if (!file_exists($path)) {
            $this->fail("Fixture file not found: {$filename}");
        }

        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = array_combine($header, $row);
            
            // Convertir empty strings a null
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }
            
            // Agregar timestamps si no existen
            if (!isset($data['created_at'])) {
                $data['created_at'] = now();
                $data['updated_at'] = now();
            }
            
            DB::table($table)->insert($data);
        }

        echo "\n📦 Cargado {$filename} -> {$table} (" . count($rows) . " rows)\n";
    }
}
