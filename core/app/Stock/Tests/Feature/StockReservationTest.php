<?php

namespace App\Stock\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Test de Reservas de Stock
 * 
 * Verifica que el sistema de reservas funciona correctamente:
 * - Reservar stock disminuye available (quantity - reserved)
 * - No se puede reservar más de lo disponible
 * - Liberar reserva restaura disponibilidad
 * - reserved_quantity se actualiza correctamente
 */
class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    // Keep property type aligned with Laravel's TestCase (no typed property).
    protected $defaultHeaders = [
        'VESSEL-ACCESS-PRIVATE' => 'test-token',
    ];

    private string $warehouseId;
    private string $itemId;
    private string $stockItemId;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('auth_access_tokens')->insert([
            'id' => Str::uuid()->toString(),
            'token' => 'test-token',
            'workspace_id' => 'ws-test',
            'scope' => 'all',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->warehouseId = '09d37adb-a0c9-499e-8ef2-c9f45d290288';
        $this->itemId = 'test-item-' . uniqid();
        $this->stockItemId = 'stock-' . uniqid();
        
        // Insertar directamente en BD (bypass Portal/Catalog validation)
        DB::table('stock_items')->insert([
            'id' => $this->stockItemId,
            'sku' => $this->itemId,
            'catalog_item_id' => $this->itemId,
            'catalog_origin' => null,
            'location_id' => $this->warehouseId,
            'location_type' => 'warehouse',
            'quantity' => 100,
            'reserved_quantity' => 0,
            'item_type' => 'unit',
            'item_id' => $this->stockItemId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_reserve_decreases_available_quantity(): void
    {
        // Reservar 30 unidades
        $response = $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", [
            'quantity' => 30,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'quantity' => 100,           // Total NO cambia
                    'reserved_quantity' => 30,    // Reservado aumenta
                ],
            ]);

        // Verificar disponible = 100 - 30 = 70
        $available = $response->json('data.quantity') - $response->json('data.reserved_quantity');
        $this->assertEquals(70, $available);
    }

    public function test_cannot_reserve_more_than_available(): void
    {
        // Primero reservar 80
        $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", [
            'quantity' => 80,
        ])->assertStatus(200);

        // Intentar reservar 30 más (solo hay 20 disponibles)
        $response = $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", [
            'quantity' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'message' => 'Cannot reserve 30 units. Only 20 available.',
            ]);
    }

    public function test_release_restores_available_quantity(): void
    {
        // Reservar 50
        $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", [
            'quantity' => 50,
        ])->assertStatus(200);

        // Liberar 20
        $response = $this->postJson("/api/v1/stock/items/release/{$this->stockItemId}", [
            'quantity' => 20,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'quantity' => 100,          // Total NO cambia
                    'reserved_quantity' => 30,   // 50 - 20 = 30
                ],
            ]);

        // Disponible ahora es 70
        $available = $response->json('data.quantity') - $response->json('data.reserved_quantity');
        $this->assertEquals(70, $available);
    }

    public function test_cannot_release_more_than_reserved(): void
    {
        // Reservar 20
        $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", [
            'quantity' => 20,
        ])->assertStatus(200);

        // Intentar liberar 30 (solo hay 20 reservadas)
        $response = $this->postJson("/api/v1/stock/items/release/{$this->stockItemId}", [
            'quantity' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'message' => 'Cannot release 30 units. Only 20 reserved.',
            ]);
    }

    public function test_multiple_reservations_accumulate(): void
    {
        // Reservar 3 veces
        $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", ['quantity' => 10])
            ->assertStatus(200);
        
        $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", ['quantity' => 15])
            ->assertStatus(200);
        
        $response = $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", ['quantity' => 25])
            ->assertStatus(200);

        // Total reservado = 10 + 15 + 25 = 50
        $response->assertJson([
            'data' => [
                'quantity' => 100,
                'reserved_quantity' => 50,
            ],
        ]);
    }

    public function test_reserve_via_movements_endpoint(): void
    {
        // También existe /api/v1/stock/movements/reserve
        $response = $this->postJson('/api/v1/stock/movements/reserve', [
            'item_id' => $this->itemId,
            'location_id' => $this->warehouseId,
            'quantity' => 25,
        ]);

        // Este endpoint crea un Movement pero NO actualiza reserved_quantity directamente
        // (es un registro histórico, el StockMovementService lo procesa)
        $response->assertStatus(201); // o el código que retorne
    }

    public function test_shipment_uses_available_not_reserved(): void
    {
        // Reservar 40 unidades
        $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", ['quantity' => 40])
            ->assertStatus(200);

        // Intentar enviar 70 (solo hay 60 disponibles = 100 - 40)
        $response = $this->postJson('/api/v1/stock/movements/shipment', [
            'item_id' => $this->itemId,
            'location_id' => $this->warehouseId,
            'quantity' => 70,
        ]);

        // Debería fallar porque reserved está bloqueado
        $response->assertStatus(422);
    }

    public function test_get_stock_item_shows_reserved_and_available(): void
    {
        // Reservar 35
        $this->postJson("/api/v1/stock/items/reserve/{$this->stockItemId}", ['quantity' => 35])
            ->assertStatus(200);

        // GET del item
        $response = $this->getJson("/api/v1/stock/items/show/{$this->stockItemId}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'quantity' => 100,
                    'reserved_quantity' => 35,
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(65, $data['quantity'] - $data['reserved_quantity'], 'Available should be 65');
    }
}
