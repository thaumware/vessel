<?php

declare(strict_types=1);

namespace App\Stock\Tests\Feature;

use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests para el comando de expiración automática de reservas.
 */
class ReservationExpirationTest extends TestCase
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
        $this->itemId = 'ITEM-EXP-001';
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

    public function test_command_expires_past_date_reservations(): void
    {
        // Crear una reserva activa con fecha de expiración pasada
        $expiredReservationId = Str::uuid()->toString();
        DB::table('stock_reservations')->insert([
            'id' => $expiredReservationId,
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 25,
            'reserved_by' => 'system',
            'reference_type' => 'test',
            'reference_id' => 'TEST-001',
            'status' => 'active',
            'expires_at' => now()->subHours(2), // Expiro hace 2 horas
            'created_at' => now()->subDays(1),
            'released_at' => null,
        ]);

        // Crear una reserva activa NO expirada
        $activeReservationId = Str::uuid()->toString();
        DB::table('stock_reservations')->insert([
            'id' => $activeReservationId,
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 15,
            'reserved_by' => 'system',
            'reference_type' => 'test',
            'reference_id' => 'TEST-002',
            'status' => 'active',
            'expires_at' => now()->addHours(24), // Expira en 24 horas
            'created_at' => now(),
            'released_at' => null,
        ]);

        // Actualizar stock item con reservas
        DB::table('stock_items')
            ->where('id', $this->stockItemId)
            ->update(['reserved_quantity' => 40]); // 25 + 15

        // Ejecutar el comando con --force para evitar prompt
        $exitCode = Artisan::call('stock:expire-reservations', ['--force' => true]);

        $this->assertEquals(0, $exitCode, 'Command should complete successfully');

        // Verificar que la reserva expirada cambió de estado
        $expired = DB::table('stock_reservations')->where('id', $expiredReservationId)->first();
        $this->assertEquals('expired', $expired->status);
        $this->assertNotNull($expired->released_at);

        // Verificar que la reserva activa NO cambió
        $active = DB::table('stock_reservations')->where('id', $activeReservationId)->first();
        $this->assertEquals('active', $active->status);
        $this->assertNull($active->released_at);

        // Verificar que el stock reservado se redujo
        $stock = DB::table('stock_items')->where('id', $this->stockItemId)->first();
        $this->assertEquals(15, $stock->reserved_quantity, 'Should only have active reservation remaining');
    }

    public function test_command_dry_run_does_not_modify_data(): void
    {
        $reservationId = Str::uuid()->toString();
        DB::table('stock_reservations')->insert([
            'id' => $reservationId,
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 30,
            'reserved_by' => 'system',
            'reference_type' => 'test',
            'reference_id' => 'DRY-001',
            'status' => 'active',
            'expires_at' => now()->subHour(),
            'created_at' => now()->subDay(),
            'released_at' => null,
        ]);

        DB::table('stock_items')
            ->where('id', $this->stockItemId)
            ->update(['reserved_quantity' => 30]);

        // Ejecutar con --dry-run
        $exitCode = Artisan::call('stock:expire-reservations', ['--dry-run' => true]);

        $this->assertEquals(0, $exitCode);

        // Verificar que NO cambió nada
        $reservation = DB::table('stock_reservations')->where('id', $reservationId)->first();
        $this->assertEquals('active', $reservation->status);
        $this->assertNull($reservation->released_at);

        $stock = DB::table('stock_items')->where('id', $this->stockItemId)->first();
        $this->assertEquals(30, $stock->reserved_quantity);
    }

    public function test_command_handles_no_expired_reservations(): void
    {
        // Sin reservas expiradas
        $reservationId = Str::uuid()->toString();
        DB::table('stock_reservations')->insert([
            'id' => $reservationId,
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 10,
            'reserved_by' => 'system',
            'reference_type' => 'test',
            'reference_id' => 'NONE-001',
            'status' => 'active',
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
            'released_at' => null,
        ]);

        $exitCode = Artisan::call('stock:expire-reservations', ['--force' => true]);

        $this->assertEquals(0, $exitCode);

        // Verificar que nada cambió
        $reservation = DB::table('stock_reservations')->where('id', $reservationId)->first();
        $this->assertEquals('active', $reservation->status);
    }

    public function test_command_creates_release_movements(): void
    {
        $reservationId = Str::uuid()->toString();
        DB::table('stock_reservations')->insert([
            'id' => $reservationId,
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 20,
            'reserved_by' => 'system',
            'reference_type' => 'test',
            'reference_id' => 'MOV-001',
            'status' => 'active',
            'expires_at' => now()->subMinutes(30),
            'created_at' => now()->subDay(),
            'released_at' => null,
        ]);

        DB::table('stock_items')
            ->where('id', $this->stockItemId)
            ->update(['reserved_quantity' => 20]);

        Artisan::call('stock:expire-reservations', ['--force' => true]);

        // Verificar que se creó un movimiento de release
        $movement = DB::table('stock_movements')
            ->where('sku', $this->itemId)
            ->where('movement_type', 'release')
            ->latest()
            ->first();

        $this->assertNotNull($movement, 'Release movement should be created');
        $this->assertEquals(20, $movement->quantity);
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
            'max_reservation_percentage' => 80,
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
                public function __construct(private string $locationId) {}
                public function getDescendantIds(string $locationId): array { return []; }
                public function getChildrenIds(string $locationId): array { return []; }
                public function getParentId(string $locationId): ?string { return null; }
                public function getAncestorIds(string $locationId): array { return []; }
                public function exists(string $locationId): bool { return $locationId === $this->locationId; }
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
