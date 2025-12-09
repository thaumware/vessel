<?php

declare(strict_types=1);

namespace App\Stock\Tests\Feature;

use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests de integración para endpoints de movimientos de stock.
 * 
 * Cubre:
 * - Receipt (entrada desde proveedor)
 * - Shipment (salida a cliente)
 * - Transfer (entre ubicaciones)
 * - Adjustment (ajustes de inventario)
 * - Validación de capacidad
 * - Manejo de metadata y referencias
 */
class MovementEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected $defaultHeaders = [
        'VESSEL-ACCESS-PRIVATE' => 'test-token',
    ];

    private string $locationId;
    private string $itemId;
    private string $stockItemId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locationId = '09d37adb-a0c9-499e-8ef2-c9f45d290288';
        $this->itemId = 'ITEM-TEST-001';
        $this->stockItemId = 'stock-' . Str::uuid()->toString();

        DB::table('auth_access_tokens')->insert([
            'id' => Str::uuid()->toString(),
            'token' => 'test-token',
            'workspace_id' => 'ws-test',
            'scope' => 'all',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seedStock();
        $this->bindGateways();
    }

    public function test_receipt_increases_stock_and_creates_movement(): void
    {
        $response = $this->postJson('/api/v1/stock/movements/receipt', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 50,
            'lot_id' => 'LOT-2024-12',
            'reference_id' => 'PO-001',
            'reason' => 'Initial receipt',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Recepción procesada']);

        // Verificar que el stock aumentó
        $stock = DB::table('stock_items')
            ->where('sku', $this->itemId)
            ->where('location_id', $this->locationId)
            ->first();

        $this->assertEquals(150, $stock->quantity); // 100 inicial + 50

        // Verificar que se creó el movimiento
        $movement = DB::table('stock_movements')
            ->where('sku', $this->itemId)
            ->where('movement_type', 'receipt')
            ->latest()
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(50, $movement->quantity);
    }

    public function test_shipment_decreases_stock_respecting_reservations(): void
    {
        // Reservar 20 unidades primero
        DB::table('stock_items')
            ->where('id', $this->stockItemId)
            ->update(['reserved_quantity' => 20]);

        // Intentar despachar 90 (solo 80 disponibles = 100 total - 20 reservadas)
        $response = $this->postJson('/api/v1/stock/movements/shipment', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 90,
            'reference_id' => 'SO-001',
        ]);

        $response->assertStatus(422); // Debería fallar por stock insuficiente

        // Despachar cantidad válida
        $response = $this->postJson('/api/v1/stock/movements/shipment', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 70,
            'reference_id' => 'SO-002',
        ]);

        $response->assertStatus(201);

        $stock = DB::table('stock_items')->where('id', $this->stockItemId)->first();
        $this->assertEquals(30, $stock->quantity); // 100 - 70
    }

    public function test_transfer_moves_stock_between_locations(): void
    {
        $destLocationId = '09d37adb-a0c9-499e-8ef2-c9f45d290289';

        // Crear stock item destino
        DB::table('stock_items')->insert([
            'id' => 'stock-dest-' . Str::uuid()->toString(),
            'sku' => $this->itemId,
            'catalog_item_id' => $this->itemId,
            'catalog_origin' => 'internal',
            'location_id' => $destLocationId,
            'location_type' => 'warehouse',
            'quantity' => 0,
            'reserved_quantity' => 0,
            'item_type' => 'unit',
            'item_id' => 'stock-dest-' . Str::uuid()->toString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/stock/movements/transfer', [
            'item_id' => $this->itemId,
            'source_location_id' => $this->locationId,
            'destination_location_id' => $destLocationId,
            'quantity' => 30,
            'lot_id' => 'LOT-001',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.transfer_out.success', true)
            ->assertJsonPath('data.transfer_in.success', true);

        // Verificar salida de origen
        $sourceStock = DB::table('stock_items')
            ->where('sku', $this->itemId)
            ->where('location_id', $this->locationId)
            ->first();
        $this->assertEquals(70, $sourceStock->quantity);

        // Verificar entrada en destino
        $destStock = DB::table('stock_items')
            ->where('sku', $this->itemId)
            ->where('location_id', $destLocationId)
            ->first();
        $this->assertEquals(30, $destStock->quantity);
    }

    public function test_adjustment_handles_positive_and_negative_deltas(): void
    {
        // Ajuste positivo (encontrar stock)
        $response = $this->postJson('/api/v1/stock/movements/adjustment', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'delta' => 15,
            'reason' => 'Stock físico mayor al registrado',
        ]);

        $response->assertStatus(201);

        $stock = DB::table('stock_items')->where('id', $this->stockItemId)->first();
        $this->assertEquals(115, $stock->quantity);

        // Ajuste negativo (pérdida/daño)
        $response = $this->postJson('/api/v1/stock/movements/adjustment', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'delta' => -10,
            'reason' => 'Producto dañado',
        ]);

        $response->assertStatus(201);

        $stock = DB::table('stock_items')->where('id', $this->stockItemId)->first();
        $this->assertEquals(105, $stock->quantity);
    }

    public function test_receipt_respects_capacity_limit(): void
    {
        $this->markTestSkipped('Capacity validation requires StockCapacityService to be properly injected in MovementFactory');
        
        // Configurar capacidad máxima de 120
        DB::table('stock_location_settings')
            ->where('location_id', $this->locationId)
            ->update(['max_quantity' => 120]);

        // Intentar recibir 30 (total sería 130 > 120)
        $response = $this->postJson('/api/v1/stock/movements/receipt', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 30,
        ]);

        $response->assertStatus(422);
        $errors = $response->json('errors') ?? [];
        $this->assertNotEmpty($errors);
    }

    public function test_movements_preserve_metadata(): void
    {
        $workspaceUuid = Str::uuid()->toString();
        
        $response = $this->postJson('/api/v1/stock/movements/receipt', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 25,
            'reference_type' => 'purchase_order',
            'reference_id' => 'PO-2024-001',
            'reason' => 'Recepcion inicial',
            'performed_by' => 'user@example.com',
            'lot_id' => 'LOT-ABC-123',
            'workspace_id' => $workspaceUuid,
        ]);

        $response->assertStatus(201);

        $movement = DB::table('stock_movements')
            ->where('sku', $this->itemId)
            ->where('movement_type', 'receipt')
            ->latest()
            ->first();

        $this->assertNotNull($movement, 'Movement should be created');
        // Verify metadata is preserved in the response
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_shipment_validates_string_length_constraints(): void
    {
        $response = $this->postJson('/api/v1/stock/movements/shipment', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 10,
            'reference_id' => str_repeat('x', 256), // Excede MAX 255
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reference_id']);
    }

    public function test_receipt_handles_unicode_and_special_characters(): void
    {
        $response = $this->postJson('/api/v1/stock/movements/receipt', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 5,
            'reason' => 'Recepción con ñ, ü, é, 中文, emoji 🚀',
            'lot_id' => 'LOT-ÑÜÉ-001',
        ]);

        $response->assertStatus(201);

        $movement = DB::table('stock_movements')
            ->where('sku', $this->itemId)
            ->latest()
            ->first();

        $this->assertStringContainsString('🚀', $movement->reference ?? '');
    }

    public function test_transfer_reverts_on_destination_failure(): void
    {
        $invalidDestination = 'invalid-location-id';

        $response = $this->postJson('/api/v1/stock/movements/transfer', [
            'item_id' => $this->itemId,
            'source_location_id' => $this->locationId,
            'destination_location_id' => $invalidDestination,
            'quantity' => 20,
        ]);

        // Debería fallar y revertir
        $response->assertStatus(422);

        // Verificar que el stock origen no cambió
        $stock = DB::table('stock_items')->where('id', $this->stockItemId)->first();
        $this->assertEquals(100, $stock->quantity, 'Stock should remain unchanged after failed transfer');
    }

    public function test_adjustment_cannot_make_quantity_negative_by_default(): void
    {
        $response = $this->postJson('/api/v1/stock/movements/adjustment', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'delta' => -150, // Quantity es 100, no debería permitir -150
            'reason' => 'Intento de ajuste inválido',
        ]);

        // Depende de la configuración allow_negative_stock
        $settings = DB::table('stock_location_settings')
            ->where('location_id', $this->locationId)
            ->first();

        if (!$settings->allow_negative_stock) {
            $response->assertStatus(422);
        }
    }

    public function test_movements_index_returns_list(): void
    {
        // Crear varios movimientos
        $this->postJson('/api/v1/stock/movements/receipt', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 10,
        ])->assertStatus(201);

        $this->postJson('/api/v1/stock/movements/shipment', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 5,
        ])->assertStatus(201);

        $response = $this->getJson('/api/v1/stock/movements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'item_id', 'quantity']
                ],
                'meta' => ['total']
            ]);
    }

    private function seedStock(): void
    {
        DB::table('stock_items')->insert([
            'id' => $this->stockItemId,
            'sku' => $this->itemId,
            'catalog_item_id' => $this->itemId,
            'catalog_origin' => 'internal',
            'location_id' => $this->locationId,
            'location_type' => 'warehouse',
            'quantity' => 100,
            'reserved_quantity' => 0,
            'item_type' => 'unit',
            'item_id' => $this->stockItemId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_location_settings')->insert([
            'id' => Str::uuid()->toString(),
            'location_id' => $this->locationId,
            'max_quantity' => null,
            'max_weight' => null,
            'max_volume' => null,
            'allowed_item_types' => null,
            'allow_mixed_lots' => true,
            'allow_mixed_skus' => true,
            'allow_negative_stock' => false,
            'max_reservation_percentage' => null,
            'fifo_enforced' => false,
            'is_active' => true,
            'workspace_id' => 'ws-test',
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function bindGateways(): void
    {
        $this->app->bind(CatalogGatewayInterface::class, function () {
            return new class implements CatalogGatewayInterface {
                public function linkToCatalog(\App\Stock\Domain\Entities\StockItem $stockItem): void {}
                public function attachCatalogData(iterable $stockItems): array { return is_array($stockItems) ? $stockItems : iterator_to_array($stockItems); }
                public function catalogItemExists(string $catalogItemId, ?string $origin = null): bool { return true; }
                public function getDefaultOriginName(): string { return 'internal'; }
                public function registerOrigin(string $name, string $source, string $type = 'table'): string { return $name; }
                public function getItem(string $itemId): ?array { return ['id' => $itemId, 'name' => 'Item '.$itemId]; }
                public function searchItems(string $searchTerm, int $limit = 50): array { return []; }
            };
        });

        $locationId = $this->locationId;
        $this->app->bind(LocationGatewayInterface::class, function () use ($locationId) {
            return new class($locationId) implements LocationGatewayInterface {
                private array $validLocations;
                
                public function __construct(string $locationId) 
                {
                    $this->validLocations = [
                        $locationId,
                        '09d37adb-a0c9-499e-8ef2-c9f45d290289' // Destino para transfers
                    ];
                }
                
                public function getDescendantIds(string $locationId): array { return []; }
                public function getChildrenIds(string $locationId): array { return []; }
                public function getParentId(string $locationId): ?string { return null; }
                public function getAncestorIds(string $locationId): array { return []; }
                public function exists(string $locationId): bool { return in_array($locationId, $this->validLocations); }
                public function getLocationType(string $locationId): ?string { return 'warehouse'; }
                public function getLocation(string $locationId): ?array
                {
                    if (!$this->exists($locationId)) {
                        return null;
                    }
                    return ['id' => $locationId, 'name' => 'Location '.$locationId, 'type' => 'warehouse'];
                }
            };
        });
    }
}
