<?php

namespace App\Catalog\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Thaumware\Support\Uuid\Uuid;

class ItemsWithStockTest extends TestCase
{
    use RefreshDatabase;

    private string $workspaceId;
    private string $locationId1;
    private string $locationId2;
    private string $termId1;
    private string $termId2;
    private string $uomId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspaceId = Uuid::v4();
        $this->locationId1 = Uuid::v4();
        $this->locationId2 = Uuid::v4();
        $this->termId1 = Uuid::v4();
        $this->termId2 = Uuid::v4();
        $this->uomId = Uuid::v4();

        // Crear UOM
        DB::table('uom_measures')->insert([
            'id' => $this->uomId,
            'code' => 'UNI',
            'name' => 'Unidad',
            'symbol' => 'un',
            'category' => null,
            'is_base' => true,
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function list_items_sin_enriquecimiento()
    {
        // Crear item
        $itemId = Uuid::v4();
        DB::table('catalog_items')->insert([
            'id' => $itemId,
            'name' => 'Mouse USB',
            'description' => 'Mouse optico',
            'uom_id' => $this->uomId,
            'status' => 'active',
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/items/read');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'uom_id', 'status', 'term_ids']
                ]
            ])
            ->assertJsonMissing(['stock_summary'])
            ->assertJsonMissing(['terms']);
    }

    /** @test */
    public function list_items_con_stock_summary()
    {
        // Crear item
        $itemId = Uuid::v4();
        DB::table('catalog_items')->insert([
            'id' => $itemId,
            'name' => 'Mouse USB',
            'description' => 'Mouse optico',
            'uom_id' => $this->uomId,
            'status' => 'active',
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear stock en dos ubicaciones
        DB::table('stock_items')->insert([
            [
                'id' => Uuid::v4(),
                'sku' => 'MOUSE-001',
                'catalog_item_id' => $itemId,
                'location_id' => $this->locationId1,
                'quantity' => 100,
                'reserved_quantity' => 20,
                'workspace_id' => $this->workspaceId,
                'catalog_origin' => 'internal',
                'location_type' => 'warehouse',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Uuid::v4(),
                'sku' => 'MOUSE-001',
                'catalog_item_id' => $itemId,
                'location_id' => $this->locationId2,
                'quantity' => 50,
                'reserved_quantity' => 10,
                'workspace_id' => $this->workspaceId,
                'catalog_origin' => 'internal',
                'location_type' => 'warehouse',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson('/api/v1/items/read?with_stock=true');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.stock_summary.total_quantity', 150)
            ->assertJsonPath('data.0.stock_summary.reserved_quantity', 30)
            ->assertJsonPath('data.0.stock_summary.available_quantity', 120)
            ->assertJsonPath('data.0.stock_summary.location_count', 2)
            ->assertJsonCount(2, 'data.0.stock_summary.locations');
    }

    /** @test */
    public function list_items_con_terms()
    {
        // Crear item
        $itemId = Uuid::v4();
        DB::table('catalog_items')->insert([
            'id' => $itemId,
            'name' => 'Mouse USB',
            'description' => 'Mouse optico',
            'uom_id' => $this->uomId,
            'status' => 'active',
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vincular con termino
        DB::table('catalog_item_terms')->insert([
            'id' => Uuid::v4(),
            'item_id' => $itemId,
            'term_id' => $this->termId1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/items/read?with_terms=true');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.term_ids.0', $this->termId1)
            ->assertJsonPath('data.0.terms.0.name', 'Electronica');
    }

    /** @test */
    public function filtra_items_por_term_id()
    {
        // Crear dos items
        $itemId1 = Uuid::v4();
        $itemId2 = Uuid::v4();
        
        DB::table('catalog_items')->insert([
            [
                'id' => $itemId1,
                'name' => 'Mouse USB',
                'description' => 'Mouse optico',
                'uom_id' => $this->uomId,
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $itemId2,
                'name' => 'Martillo',
                'description' => 'Martillo de goma',
                'uom_id' => $this->uomId,
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Vincular items con diferentes terminos
        DB::table('catalog_item_terms')->insert([
            [
                'id' => Uuid::v4(),
                'item_id' => $itemId1,
                'term_id' => $this->termId1, // Electronica
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Uuid::v4(),
                'item_id' => $itemId2,
                'term_id' => $this->termId2, // Ferreteria
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson("/api/v1/items/read?term_id={$this->termId1}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mouse USB');
    }

    /** @test */
    public function filtra_items_por_location_id()
    {
        // Crear dos items
        $itemId1 = Uuid::v4();
        $itemId2 = Uuid::v4();
        
        DB::table('catalog_items')->insert([
            [
                'id' => $itemId1,
                'name' => 'Mouse USB',
                'description' => 'Mouse optico',
                'uom_id' => $this->uomId,
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $itemId2,
                'name' => 'Teclado',
                'description' => 'Teclado mecanico',
                'uom_id' => $this->uomId,
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Solo itemId1 tiene stock en locationId1
        DB::table('stock_items')->insert([
            'id' => Uuid::v4(),
            'sku' => 'MOUSE-001',
            'catalog_item_id' => $itemId1,
            'location_id' => $this->locationId1,
            'quantity' => 100,
            'reserved_quantity' => 0,
            'workspace_id' => $this->workspaceId,
            'catalog_origin' => 'internal',
            'location_type' => 'warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/items/read?location_id={$this->locationId1}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mouse USB');
    }

    /** @test */
    public function filtra_items_con_stock_bajo()
    {
        // Crear dos items
        $itemId1 = Uuid::v4();
        $itemId2 = Uuid::v4();
        
        DB::table('catalog_items')->insert([
            [
                'id' => $itemId1,
                'name' => 'Mouse USB',
                'description' => 'Mouse optico',
                'uom_id' => $this->uomId,
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $itemId2,
                'name' => 'Teclado',
                'description' => 'Teclado mecanico',
                'uom_id' => $this->uomId,
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Item1 con stock bajo, Item2 con stock alto
        DB::table('stock_items')->insert([
            [
                'id' => Uuid::v4(),
                'sku' => 'MOUSE-001',
                'catalog_item_id' => $itemId1,
                'location_id' => $this->locationId1,
                'quantity' => 3,
                'reserved_quantity' => 0,
                'workspace_id' => $this->workspaceId,
                'catalog_origin' => 'internal',
                'location_type' => 'warehouse',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Uuid::v4(),
                'sku' => 'TECLADO-001',
                'catalog_item_id' => $itemId2,
                'location_id' => $this->locationId1,
                'quantity' => 100,
                'reserved_quantity' => 0,
                'workspace_id' => $this->workspaceId,
                'catalog_origin' => 'internal',
                'location_type' => 'warehouse',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson('/api/v1/items/read?low_stock=true&max_quantity=5');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mouse USB')
            ->assertJsonPath('data.0.stock_summary.available_quantity', 3);
    }

    /** @test */
    public function combina_filtros_y_enriquecimientos()
    {
        // Crear item
        $itemId = Uuid::v4();
        DB::table('catalog_items')->insert([
            'id' => $itemId,
            'name' => 'Mouse USB',
            'description' => 'Mouse optico',
            'uom_id' => $this->uomId,
            'status' => 'active',
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vincular termino
        DB::table('catalog_item_terms')->insert([
            'id' => Uuid::v4(),
            'item_id' => $itemId,
            'term_id' => $this->termId1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear stock
        DB::table('stock_items')->insert([
            'id' => Uuid::v4(),
            'sku' => 'MOUSE-001',
            'catalog_item_id' => $itemId,
            'location_id' => $this->locationId1,
            'quantity' => 100,
            'reserved_quantity' => 20,
            'workspace_id' => $this->workspaceId,
            'catalog_origin' => 'internal',
            'location_type' => 'warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/items/read?with_stock=true&with_terms=true&term_id={$this->termId1}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mouse USB')
            ->assertJsonPath('data.0.stock_summary.total_quantity', 100)
            ->assertJsonPath('data.0.terms.0.name', 'Electronica');
    }
}
