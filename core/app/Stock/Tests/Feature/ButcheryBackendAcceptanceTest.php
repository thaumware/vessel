<?php

declare(strict_types=1);

namespace App\Stock\Tests\Feature;

use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\LotRepositoryInterface;
use App\Stock\Infrastructure\Out\InMemory\InMemoryLotRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ButcheryBackendAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected $defaultHeaders = [
        'VESSEL-ACCESS-PRIVATE' => 'test-token',
    ];

    private string $workspaceId;
    private string $coldRoomId;
    private string $displayId;
    private string $freezerId;
    private string $processRoomId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspaceId = 'ws-butcher';
        $this->coldRoomId = '09d37adb-a0c9-499e-8ef2-c9f45d290281';
        $this->displayId = '09d37adb-a0c9-499e-8ef2-c9f45d290282';
        $this->freezerId = '09d37adb-a0c9-499e-8ef2-c9f45d290283';
        $this->processRoomId = '09d37adb-a0c9-499e-8ef2-c9f45d290284';

        DB::table('auth_access_tokens')->insert([
            'id' => (string) Str::uuid(),
            'token' => 'test-token',
            'workspace_id' => $this->workspaceId,
            'scope' => 'all',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->bindCatalogGateway();
        $this->bindPersistentLotRepository();
        $this->bindLocationGateway();
    }

    public function test_recepcion_de_cerdo_por_lote(): void
    {
        $itemId = 'cerdo-canal';
        $this->createCatalogItem($itemId, 'Cerdo Canal', 'uom-kg');

        $response = $this->postJson('/api/v1/stock/movements/receipt', [
            'item_id' => $itemId,
            'location_id' => $this->coldRoomId,
            'quantity' => 110,
            'lot_id' => 'LOT-CERDO-001',
            'reference_id' => 'PO-CERDO-001',
            'reason' => 'Ingreso de cerdo completo',
            'workspace_id' => '018db9d3-7a82-7001-8000-000000000111',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.success', true);

        $stock = DB::table('stock_items')
            ->where('sku', $itemId)
            ->where('location_id', $this->coldRoomId)
            ->first();

        $movement = DB::table('stock_movements')
            ->where('sku', $itemId)
            ->where('movement_type', 'receipt')
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(110, (float) $stock->quantity);
        $this->assertNotNull($movement);
        $this->assertEquals(110, (float) $movement->quantity);
        $this->assertEquals('LOT-CERDO-001', $stock->lot_number);
    }

    public function test_merma_por_limpieza_rebaja_stock(): void
    {
        $itemId = 'costillar-cerdo';
        $this->createCatalogItem($itemId, 'Costillar de Cerdo', 'uom-kg');
        $stockItemId = $this->createStockItem($itemId, $this->coldRoomId, 40, 0);

        $response = $this->postJson('/api/v1/stock/movements/adjustment', [
            'item_id' => $itemId,
            'location_id' => $this->coldRoomId,
            'delta' => -5,
            'reason' => 'Merma por limpieza y recorte',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Ajuste procesado']);

        $stock = DB::table('stock_items')->where('id', $stockItemId)->first();
        $movement = DB::table('stock_movements')
            ->where('sku', $itemId)
            ->where('movement_type', 'adjustment_out')
            ->first();

        $this->assertEquals(35, (float) $stock->quantity);
        $this->assertNotNull($movement);
        $this->assertEquals(5, (float) $movement->quantity);
    }

    public function test_lotes_y_alerta_de_vencimiento(): void
    {
        $itemId = 'pechuga-pollo';
        $this->createCatalogItem($itemId, 'Pechuga de Pollo', 'uom-kg');

        $create = $this->postJson('/api/v1/stock/lots/create', [
            'item_id' => $itemId,
            'lot_number' => 'LOT-POLLO-EXP-01',
            'expiration_date' => now()->addDays(7)->format('Y-m-d'),
            'production_date' => now()->subDays(1)->format('Y-m-d'),
            'source_type' => 'supplier',
            'source_id' => (string) Str::uuid(),
            'supplier_lot_number' => 'SUP-CHICK-77',
        ]);

        $create->assertStatus(201)
            ->assertJsonFragment(['message' => 'Lote creado exitosamente']);

        $lotId = $create->json('data.id');

        $show = $this->getJson('/api/v1/stock/lots/by-number/LOT-POLLO-EXP-01');
        $show->assertStatus(200)
            ->assertJsonPath('data.id', $lotId)
            ->assertJsonPath('data.source_type', 'supplier');

        $expiring = $this->getJson('/api/v1/stock/lots/expiring-soon?days=30');
        $expiring->assertStatus(200)
            ->assertJsonPath('meta.total', 1);

        $returnedLotNumbers = array_column($expiring->json('data'), 'lot_number');
        $this->assertContains('LOT-POLLO-EXP-01', $returnedLotNumbers);
    }

    public function test_transferencia_entre_camara_y_mostrador(): void
    {
        $itemId = 'asado-tira-vacuno';
        $this->createCatalogItem($itemId, 'Asado de Tira de Vacuno', 'uom-kg');
        $this->createStockItem($itemId, $this->coldRoomId, 25, 0);
        $this->createStockItem($itemId, $this->displayId, 0, 0);

        $response = $this->postJson('/api/v1/stock/movements/transfer', [
            'item_id' => $itemId,
            'source_location_id' => $this->coldRoomId,
            'destination_location_id' => $this->displayId,
            'quantity' => 10,
            'lot_id' => 'LOT-VACUNO-TR-01',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.transfer_out.success', true)
            ->assertJsonPath('data.transfer_in.success', true);

        $source = DB::table('stock_items')
            ->where('sku', $itemId)
            ->where('location_id', $this->coldRoomId)
            ->first();

        $destination = DB::table('stock_items')
            ->where('sku', $itemId)
            ->where('location_id', $this->displayId)
            ->first();

        $this->assertEquals(15, (float) $source->quantity);
        $this->assertEquals(10, (float) $destination->quantity);
    }

    public function test_reserva_liberacion_y_venta(): void
    {
        $itemId = 'lomo-vetado-vacuno';
        $this->createCatalogItem($itemId, 'Lomo Vetado de Vacuno', 'uom-kg');
        $stockItemId = $this->createStockItem($itemId, $this->displayId, 30, 0);
        $this->createLocationSettings($this->displayId, ['max_reservation_percentage' => 80]);

        $reserve = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => $itemId,
            'location_id' => $this->displayId,
            'quantity' => 10,
            'reference_type' => 'sales_order',
            'reference_id' => 'SO-LOMO-001',
        ]);

        $reserve->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('new_reserved_quantity', 10);

        $reservationId = $reserve->json('reservation_id');

        $blockedShipment = $this->postJson('/api/v1/stock/movements/shipment', [
            'item_id' => $itemId,
            'location_id' => $this->displayId,
            'quantity' => 25,
            'reference_id' => 'SALE-OVER-001',
        ]);

        $blockedShipment->assertStatus(422);

        $release = $this->postJson('/api/v1/stock/reservations/release', [
            'item_id' => $itemId,
            'location_id' => $this->displayId,
            'quantity' => 5,
            'reservation_id' => $reservationId,
            'reference_id' => 'SO-LOMO-001',
        ]);

        $release->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('new_reserved_quantity', 5);

        $shipment = $this->postJson('/api/v1/stock/movements/shipment', [
            'item_id' => $itemId,
            'location_id' => $this->displayId,
            'quantity' => 20,
            'reference_id' => 'SALE-OK-001',
        ]);

        $shipment->assertStatus(201);

        $stock = DB::table('stock_items')->where('id', $stockItemId)->first();
        $this->assertEquals(10, (float) $stock->quantity);
        $this->assertEquals(5, (float) $stock->reserved_quantity);
    }

    public function test_capacidad_de_camara_fria(): void
    {
        $itemId = 'panceta-cerdo';
        $this->bindLocationGateway();
        $this->createCurrentStock($itemId, $this->coldRoomId, 80);

        $store = $this->postJson('/api/v1/stock/capacity', [
            'location_id' => $this->coldRoomId,
            'max_quantity' => 100,
            'allow_mixed_skus' => false,
            'allow_mixed_lots' => true,
            'is_active' => true,
        ]);

        $store->assertStatus(200)
            ->assertJsonPath('data.max_quantity', 100);

        $stats = $this->getJson('/api/v1/stock/capacity/' . $this->coldRoomId . '/stats');
        $stats->assertStatus(200)
            ->assertJsonPath('data.current_quantity', 80)
            ->assertJsonPath('data.max_quantity', 100)
            ->assertJsonPath('data.available_quantity', 20)
            ->assertJsonPath('data.usage_percent', 80);

        $available = $this->getJson('/api/v1/stock/capacity/' . $this->coldRoomId . '/available');
        $available->assertStatus(200)
            ->assertJsonPath('data.available_capacity', 20);

        $canAccept = $this->getJson('/api/v1/stock/capacity/' . $this->coldRoomId . '/can-accept?item_id=' . $itemId . '&quantity=25');
        $canAccept->assertStatus(200)
            ->assertJsonPath('is_valid', false)
            ->assertJsonPath('error_code', 'EXCEEDS_MAX_QUANTITY');
    }

    public function test_reporte_resumen_por_ubicacion(): void
    {
        $itemId = 'pollo-entero';
        $this->createCatalogItem($itemId, 'Pollo Entero', 'uom-kg');
        $this->createStockItem($itemId, $this->coldRoomId, 20, 0);
        $this->createStockItem($itemId, $this->freezerId, 15, 5);
        $this->bindLocationGateway([
            $this->coldRoomId => [$this->freezerId],
        ]);

        $response = $this->getJson('/api/v1/stock/locations/' . $this->coldRoomId . '/summary?include_children=true');

        $response->assertStatus(200)
            ->assertJsonPath('location_id', $this->coldRoomId)
            ->assertJsonPath('total_locations', 2)
            ->assertJsonPath('items.0.total_quantity', 35)
            ->assertJsonPath('items.0.reserved_quantity', 5)
            ->assertJsonPath('items.0.available_quantity', 30)
            ->assertJsonPath('items.0.catalog_item.uom_symbol', 'kg');
    }

    public function test_busqueda_de_catalogo_para_carniceria(): void
    {
        $pechugaId = 'pechuga-pollo-search';
        $longoId = 'longaniza-cerdo-search';

        $this->createCatalogItem($pechugaId, 'Pechuga de Pollo', 'uom-kg', 'Corte fresco de pollo');
        $this->createCatalogItem($longoId, 'Longaniza de Cerdo', 'uom-kg', 'Elaborado listo para venta');
        $this->createStockItem($pechugaId, $this->displayId, 18, 3);

        $response = $this->getJson('/api/v1/stock/catalog/search?q=Pechuga');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('data.0.name', 'Pechuga de Pollo')
            ->assertJsonPath('data.0.stock.total_quantity', 18)
            ->assertJsonPath('data.0.stock.available_quantity', 15)
            ->assertJsonPath('data.0.stock.reserved_quantity', 3);
    }

    public function test_movimiento_production_no_transforma_materia_prima(): void
    {
        $sourceId = 'carne-embutidos-cerdo';
        $outputId = 'longaniza-cerdo';

        $this->createCatalogItem($sourceId, 'Carne para Embutidos de Cerdo', 'uom-kg');
        $this->createCatalogItem($outputId, 'Longaniza de Cerdo', 'uom-kg');
        $this->createStockItem($sourceId, $this->processRoomId, 50, 0);
        $this->createStockItem($outputId, $this->processRoomId, 0, 0);

        $response = $this->postJson('/api/v1/stock/movements', [
            'type' => 'production',
            'item_id' => $outputId,
            'location_id' => $this->processRoomId,
            'quantity' => 20,
            'reference_type' => 'production_batch',
            'reference_id' => 'BATCH-LONGA-001',
            'reason' => 'Produccion de longaniza',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Movimiento procesado']);

        $source = DB::table('stock_items')
            ->where('sku', $sourceId)
            ->where('location_id', $this->processRoomId)
            ->first();

        $output = DB::table('stock_items')
            ->where('sku', $outputId)
            ->where('location_id', $this->processRoomId)
            ->first();

        $this->assertEquals(50, (float) $source->quantity);
        $this->assertEquals(20, (float) $output->quantity);
    }

    public function test_api_de_pricing_no_esta_disponible(): void
    {
        $response = $this->getJson('/api/v1/pricing/prices');

        $response->assertStatus(404);
    }

    private function bindCatalogGateway(): void
    {
        $this->app->bind(CatalogGatewayInterface::class, function () {
            return new class implements CatalogGatewayInterface {
                public function linkToCatalog(StockItem $stockItem): void
                {
                }

                public function attachCatalogData(iterable $stockItems): array
                {
                    $result = [];

                    foreach ($stockItems as $stockItem) {
                        $itemId = $stockItem->getItemId();
                        $catalogItem = \Illuminate\Support\Facades\DB::table('catalog_items')
                            ->where('id', $itemId)
                            ->first();

                        $uomId = $catalogItem->uom_id ?? null;

                        $result[] = [
                            'item_id' => $itemId,
                            'catalog_item' => [
                                'name' => $catalogItem->name ?? null,
                                'uom_id' => $uomId ?? 'unknown',
                                'uom_symbol' => $uomId === 'uom-unit' ? 'u' : 'kg',
                            ],
                        ];
                    }

                    return $result;
                }

                public function catalogItemExists(string $catalogItemId, ?string $origin = null): bool
                {
                    return \Illuminate\Support\Facades\DB::table('catalog_items')
                        ->where('id', $catalogItemId)
                        ->exists();
                }

                public function getItem(string $itemId): ?array
                {
                    $catalogItem = \Illuminate\Support\Facades\DB::table('catalog_items')
                        ->where('id', $itemId)
                        ->first();

                    if ($catalogItem === null) {
                        return null;
                    }

                    return [
                        'id' => $catalogItem->id,
                        'name' => $catalogItem->name,
                        'description' => $catalogItem->description,
                    ];
                }

                public function searchItems(string $searchTerm, int $limit = 50): array
                {
                    return \Illuminate\Support\Facades\DB::table('catalog_items')
                        ->where(function ($query) use ($searchTerm) {
                            $query->where('name', 'like', '%' . $searchTerm . '%')
                                ->orWhere('description', 'like', '%' . $searchTerm . '%');
                        })
                        ->orderBy('name')
                        ->limit($limit)
                        ->get(['id', 'name', 'description'])
                        ->map(fn ($item) => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                        ])
                        ->all();
                }

                public function getDefaultOriginName(): string
                {
                    return 'internal_catalog';
                }

                public function registerOrigin(string $name, string $source, string $type = 'table'): string
                {
                    return $name;
                }
            };
        });
    }

    private function bindPersistentLotRepository(): void
    {
        $repository = new InMemoryLotRepository();

        $this->app->singleton(LotRepositoryInterface::class, function () use ($repository) {
            return $repository;
        });
    }

    private function bindLocationGateway(array $descendantsByParent = []): void
    {
        $knownLocations = [
            $this->coldRoomId,
            $this->displayId,
            $this->freezerId,
            $this->processRoomId,
        ];

        foreach ($descendantsByParent as $parent => $children) {
            $knownLocations[] = $parent;
            foreach ($children as $child) {
                $knownLocations[] = $child;
            }
        }

        $knownLocations = array_values(array_unique($knownLocations));

        $this->app->bind(LocationGatewayInterface::class, function () use ($descendantsByParent, $knownLocations) {
            return new class($descendantsByParent, $knownLocations) implements LocationGatewayInterface {
                public function __construct(
                    private array $descendantsByParent,
                    private array $knownLocations
                ) {
                }

                public function getDescendantIds(string $locationId): array
                {
                    return $this->descendantsByParent[$locationId] ?? [];
                }

                public function getChildrenIds(string $locationId): array
                {
                    return $this->getDescendantIds($locationId);
                }

                public function getParentId(string $locationId): ?string
                {
                    foreach ($this->descendantsByParent as $parent => $children) {
                        if (in_array($locationId, $children, true)) {
                            return $parent;
                        }
                    }

                    return null;
                }

                public function getAncestorIds(string $locationId): array
                {
                    $ancestors = [];
                    $current = $locationId;

                    while (($parent = $this->getParentId($current)) !== null) {
                        $ancestors[] = $parent;
                        $current = $parent;
                    }

                    return $ancestors;
                }

                public function exists(string $locationId): bool
                {
                    return in_array($locationId, $this->knownLocations, true);
                }

                public function getLocationType(string $locationId): ?string
                {
                    return 'warehouse';
                }

                public function getLocation(string $locationId): ?array
                {
                    if (!$this->exists($locationId)) {
                        return null;
                    }

                    return [
                        'id' => $locationId,
                        'name' => 'Location ' . $locationId,
                        'type' => 'warehouse',
                    ];
                }
            };
        });
    }

    private function createCatalogItem(
        string $id,
        string $name,
        ?string $uomId = null,
        ?string $description = null
    ): void {
        DB::table('catalog_items')->insert([
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'uom_id' => $uomId,
            'notes' => null,
            'status' => 'active',
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createStockItem(
        string $itemId,
        string $locationId,
        float $quantity,
        float $reservedQuantity,
        ?string $lotNumber = null
    ): string {
        $id = 'stock-' . Str::uuid()->toString();

        DB::table('stock_items')->insert([
            'id' => $id,
            'sku' => $itemId,
            'catalog_item_id' => $itemId,
            'catalog_origin' => 'internal_catalog',
            'location_id' => $locationId,
            'location_type' => 'warehouse',
            'quantity' => $quantity,
            'reserved_quantity' => $reservedQuantity,
            'lot_number' => $lotNumber,
            'item_type' => 'unit',
            'item_id' => $itemId,
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createCurrentStock(string $sku, string $locationId, int $quantity): void
    {
        DB::table('stock_current')->insert([
            'id' => (string) Str::uuid(),
            'sku' => $sku,
            'location_id' => $locationId,
            'location_type' => 'warehouse',
            'quantity' => $quantity,
            'workspace_id' => $this->workspaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createLocationSettings(string $locationId, array $overrides = []): void
    {
        DB::table('stock_location_settings')->insert(array_merge([
            'id' => (string) Str::uuid(),
            'location_id' => $locationId,
            'max_quantity' => null,
            'storage_uom_id' => null,
            'max_weight' => null,
            'max_volume' => null,
            'allowed_item_types' => null,
            'allow_mixed_lots' => true,
            'allow_mixed_skus' => true,
            'allow_negative_stock' => false,
            'max_reservation_percentage' => null,
            'fifo_enforced' => false,
            'is_active' => true,
            'workspace_id' => $this->workspaceId,
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
