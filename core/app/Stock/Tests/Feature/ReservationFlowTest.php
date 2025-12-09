<?php

declare(strict_types=1);

namespace App\Stock\Tests\Feature;

use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    // Keep property type aligned with Laravel's TestCase (no typed property).
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
        $this->itemId = 'ITEM-001';
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

    public function test_validate_shows_available_and_catalog_data(): void
    {
        $response = $this->postJson('/api/v1/stock/reservations/validate', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 30,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'can_reserve' => true,
                'available_quantity' => 80.0,
                'reserved_quantity' => 20.0,
                'total_quantity' => 100.0,
                'max_reservation_allowed' => 80.0,
                'errors' => [],
            ]);

        $this->assertArrayHasKey('name', $response->json('item_info'));
        $this->assertArrayHasKey('name', $response->json('location_info'));
    }

    public function test_reserve_creates_record_and_updates_reserved_quantity(): void
    {
        $response = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 25,
            'reference_type' => 'sales_order',
            'reference_id' => 'SO-2024-001',
            'reason' => 'Reserva para venta',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'new_reserved_quantity' => 45.0,
                'new_available_quantity' => 55.0,
            ]);

        $this->assertNotEmpty($response->json('reservation_id'));

        $reservationRow = DB::table('stock_reservations')->first();
        $this->assertNotNull($reservationRow, 'Reservation should be persisted');
        $this->assertEquals('active', $reservationRow->status);
    }

    public function test_reserve_rejected_when_stock_insufficient(): void
    {
        $response = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 90,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        $errors = $response->json('errors') ?? [];
        $this->assertNotEmpty($errors);
    }

    public function test_reserve_rejected_when_exceeding_max_percentage(): void
    {
        $response = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 70, // 20 already reserved, would reach 90/100 > 80%
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        $errors = $response->json('errors') ?? [];
        $this->assertNotEmpty($errors, 'Should have validation errors');
        // Verificar que el mensaje menciona el limite
        $errorText = strtolower(implode(' ', $errors));
        $this->assertTrue(
            str_contains($errorText, 'limite') || str_contains($errorText, 'excede'),
            'Error should mention limit or exceeds'
        );
    }

    public function test_release_updates_reserved_and_marks_reservation_released(): void
    {
        $reservation = $this->createReservation(30);

        $response = $this->postJson('/api/v1/stock/reservations/release', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 15,
            'reservation_id' => $reservation['reservation_id'],
            'reference_id' => 'SO-2024-001',
            'reason' => 'Partial cancellation',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'new_reserved_quantity' => 35.0, // 20 base + 30 - 15
            ]);

        $row = DB::table('stock_reservations')->where('id', $reservation['reservation_id'])->first();
        $this->assertEquals('released', $row->status);
    }

    public function test_index_lists_only_active_reservations(): void
    {
        $reservationA = $this->createReservation(10);
        $reservationB = $this->createReservation(15);

        $this->postJson('/api/v1/stock/reservations/release', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => 15,
            'reservation_id' => $reservationB['reservation_id'],
        ])->assertStatus(200);

        $response = $this->getJson('/api/v1/stock/reservations');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data, 'Only active reservations should be listed');
        $this->assertEquals($reservationA['reservation_id'], $data[0]['id']);
    }

    public function test_destroy_cancels_reservation_and_restores_available(): void
    {
        $reservation = $this->createReservation(20);

        $response = $this->deleteJson('/api/v1/stock/reservations/' . $reservation['reservation_id']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'new_reserved_quantity' => 20.0,
                'new_available_quantity' => 80.0,
            ]);

        $row = DB::table('stock_reservations')->where('id', $reservation['reservation_id'])->first();
        $this->assertEquals('released', $row->status);
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
            'reserved_quantity' => 20,
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

                    return [
                        'id' => $locationId,
                        'name' => 'Warehouse '.$locationId,
                        'type' => 'warehouse',
                    ];
                }
            };
        });
    }

    private function createReservation(float $quantity): array
    {
        $response = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => $this->itemId,
            'location_id' => $this->locationId,
            'quantity' => $quantity,
            'reference_id' => 'REF-' . Str::random(6),
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);

        return $response->json();
    }
}
