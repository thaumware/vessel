<?php

namespace App\Catalog\Tests\Feature;

use App\Catalog\Infrastructure\Out\Database\Seeders\ButcheryCatalogSeeder;
use App\Uom\Infrastructure\Out\Database\Seeders\UomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ButcheryCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => 'app/Catalog/Infrastructure/Migrations', '--realpath' => false]);
        $this->artisan('migrate', ['--path' => 'app/Taxonomy/Infrastructure/Migrations', '--realpath' => false]);
        $this->artisan('migrate', ['--path' => 'app/Uom/Infrastructure/Out/Database/Migrations', '--realpath' => false]);

        $this->seed(UomSeeder::class);
    }

    public function test_seeder_creates_butchery_vocabularies_items_and_relationships(): void
    {
        $data = require base_path('app/Catalog/Infrastructure/Out/Data/butchery_catalog.php');

        $this->seed(ButcheryCatalogSeeder::class);

        $expectedItemCount = count($data['items']);

        $this->assertDatabaseHas('taxonomy_vocabularies', ['slug' => 'especies-carniceria']);
        $this->assertDatabaseHas('taxonomy_vocabularies', ['slug' => 'tipos-corte-carniceria']);
        $this->assertEquals($expectedItemCount, DB::table('catalog_items')->count());
        $this->assertEquals($expectedItemCount * 2, DB::table('catalog_item_terms')->count());

        $this->assertDatabaseHas('catalog_items', ['name' => 'Pechuga de Pollo', 'uom_id' => 'uom-kg']);
        $this->assertDatabaseHas('catalog_items', ['name' => 'Pollo Entero', 'uom_id' => 'uom-unit']);
        $this->assertDatabaseHas('catalog_items', ['name' => 'Lomo Vetado de Vacuno', 'uom_id' => 'uom-kg']);
        $this->assertDatabaseHas('catalog_terms', ['slug' => 'cerdo']);
        $this->assertDatabaseHas('catalog_terms', ['slug' => 'elaborado']);
    }

    public function test_seeder_is_idempotent(): void
    {
        $data = require base_path('app/Catalog/Infrastructure/Out/Data/butchery_catalog.php');

        $this->seed(ButcheryCatalogSeeder::class);
        $this->seed(ButcheryCatalogSeeder::class);

        $expectedItemCount = count($data['items']);

        $this->assertEquals($expectedItemCount, DB::table('catalog_items')->count());
        $this->assertEquals($expectedItemCount * 2, DB::table('catalog_item_terms')->count());
        $this->assertEquals(3, DB::table('catalog_terms')->whereIn('slug', ['cerdo', 'pollo', 'vacuno'])->count());
        $this->assertEquals(4, DB::table('catalog_terms')->whereIn('slug', ['corte-directo', 'desposte-fino', 'subproducto', 'elaborado'])->count());
    }

    public function test_seeder_backfills_uom_for_existing_items_when_uom_module_is_available(): void
    {
        DB::table('catalog_items')->insert([
            'id' => 'existing-pechuga',
            'name' => 'Pechuga de Pollo',
            'description' => null,
            'uom_id' => null,
            'notes' => null,
            'status' => 'draft',
            'workspace_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(ButcheryCatalogSeeder::class);

        $this->assertDatabaseHas('catalog_items', [
            'id' => 'existing-pechuga',
            'name' => 'Pechuga de Pollo',
            'uom_id' => 'uom-kg',
            'status' => 'active',
        ]);
    }
}
