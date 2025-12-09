<?php

namespace App\Stock\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Test de integración para el endpoint de búsqueda de catálogo con stock.
 */
class CatalogSearchTest extends TestCase
{
    use RefreshDatabase;

    protected $defaultHeaders = [
        'VESSEL-ACCESS-PRIVATE' => 'test-token',
    ];

    private string $catalogItemId1;
    private string $catalogItemId2;
    private string $catalogItemId3;
    private string $locationId;
    private string $workspaceId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspaceId = 'ws-test';
        $this->catalogItemId1 = Str::uuid()->toString();
        $this->catalogItemId2 = Str::uuid()->toString();
        $this->catalogItemId3 = Str::uuid()->toString();
        $this->locationId = Str::uuid()->toString();

        // Crear token de acceso
        DB::table('auth_access_tokens')->insert([
            'id' => Str::uuid()->toString(),
            'token' => 'test-token',
            'workspace_id' => $this->workspaceId,
            'scope' => 'all',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear items en el catálogo
        DB::table('catalog_items')->insert([
            [
                'id' => $this->catalogItemId1,
                'name' => 'Laptop Dell Inspiron',
                'description' => 'Laptop para desarrollo',
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->catalogItemId2,
                'name' => 'Mouse Logitech MX',
                'description' => 'Mouse inalambrico ergonomico',
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->catalogItemId3,
                'name' => 'Teclado Mecanico',
                'description' => 'Teclado RGB para gaming',
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Crear stock para algunos items
        DB::table('stock_items')->insert([
            [
                'id' => Str::uuid()->toString(),
                'sku' => 'LAPTOP-001',
                'catalog_item_id' => $this->catalogItemId1,
                'catalog_origin' => 'internal_catalog',
                'location_id' => $this->locationId,
                'location_type' => 'warehouse',
                'quantity' => 10,
                'reserved_quantity' => 3,
                'item_type' => 'unit',
                'item_id' => $this->catalogItemId1,
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'sku' => 'MOUSE-001',
                'catalog_item_id' => $this->catalogItemId2,
                'catalog_origin' => 'internal_catalog',
                'location_id' => $this->locationId,
                'location_type' => 'warehouse',
                'quantity' => 25,
                'reserved_quantity' => 5,
                'item_type' => 'unit',
                'item_id' => $this->catalogItemId2,
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /** @test */
    public function busca_items_por_nombre()
    {
        $response = $this->getJson('/api/v1/stock/catalog/search?q=Laptop');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'stock' => [
                            'total_quantity',
                            'available_quantity',
                            'reserved_quantity',
                            'locations',
                        ],
                    ],
                ],
                'count',
            ]);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));
        
        // Verificar que encontró la laptop
        $laptop = collect($data)->firstWhere('id', $this->catalogItemId1);
        $this->assertNotNull($laptop);
        $this->assertEquals('Laptop Dell Inspiron', $laptop['name']);
        $this->assertEquals(10, $laptop['stock']['total_quantity']);
        $this->assertEquals(7, $laptop['stock']['available_quantity']);
        $this->assertEquals(3, $laptop['stock']['reserved_quantity']);
    }

    /** @test */
    public function busca_items_por_descripcion()
    {
        $response = $this->getJson('/api/v1/stock/catalog/search?q=inalambrico');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));
        
        // Verificar que encontró el mouse
        $mouse = collect($data)->firstWhere('id', $this->catalogItemId2);
        $this->assertNotNull($mouse);
        $this->assertEquals('Mouse Logitech MX', $mouse['name']);
        $this->assertEquals(25, $mouse['stock']['total_quantity']);
        $this->assertEquals(20, $mouse['stock']['available_quantity']);
    }

    /** @test */
    public function items_sin_stock_muestran_cero()
    {
        $response = $this->getJson('/api/v1/stock/catalog/search?q=Teclado');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));
        
        // Verificar que el teclado aparece pero sin stock
        $teclado = collect($data)->firstWhere('id', $this->catalogItemId3);
        $this->assertNotNull($teclado);
        $this->assertEquals('Teclado Mecanico', $teclado['name']);
        $this->assertEquals(0, $teclado['stock']['total_quantity']);
        $this->assertEquals(0, $teclado['stock']['available_quantity']);
        $this->assertEmpty($teclado['stock']['locations']);
    }

    /** @test */
    public function respeta_limite_de_resultados()
    {
        // Crear más items para probar el límite
        for ($i = 4; $i <= 10; $i++) {
            DB::table('catalog_items')->insert([
                'id' => Str::uuid()->toString(),
                'name' => "Item Test $i",
                'description' => 'Descripcion generica',
                'status' => 'active',
                'workspace_id' => $this->workspaceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->getJson('/api/v1/stock/catalog/search?q=Item&limit=5');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertLessThanOrEqual(5, count($data));
    }

    /** @test */
    public function requiere_parametro_q()
    {
        $response = $this->getJson('/api/v1/stock/catalog/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    /** @test */
    public function busqueda_vacia_retorna_array_vacio()
    {
        $response = $this->getJson('/api/v1/stock/catalog/search?q=NoExiste12345XYZ');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
                'count' => 0,
            ]);
    }

    /** @test */
    public function agrupa_stock_por_ubicaciones()
    {
        $locationId2 = Str::uuid()->toString();

        // Agregar stock del mismo item en otra ubicación
        DB::table('stock_items')->insert([
            'id' => Str::uuid()->toString(),
            'sku' => 'LAPTOP-002',
            'catalog_item_id' => $this->catalogItemId1,
            'catalog_origin' => 'internal_catalog',
            'location_id' => $locationId2,
            'location_type' => 'warehouse',
            'quantity' => 5,
            'reserved_quantity' => 1,
            'item_type' => 'unit',
            'item_id' => $this->catalogItemId1,
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/stock/catalog/search?q=Laptop');

        $response->assertStatus(200);

        $data = $response->json('data');
        $laptop = collect($data)->firstWhere('id', $this->catalogItemId1);

        // Debe mostrar totales agregados
        $this->assertEquals(15, $laptop['stock']['total_quantity']); // 10 + 5
        $this->assertEquals(11, $laptop['stock']['available_quantity']); // 7 + 4
        $this->assertEquals(4, $laptop['stock']['reserved_quantity']); // 3 + 1

        // Debe tener 2 ubicaciones
        $this->assertCount(2, $laptop['stock']['locations']);
    }
}
