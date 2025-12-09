<?php

namespace App\Stock\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Stock\Domain\ReservationStatus;

class ReservationApprovalTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup Catalog Mock
        $this->instance(
            \App\Stock\Domain\Interfaces\CatalogGatewayInterface::class,
            new class implements \App\Stock\Domain\Interfaces\CatalogGatewayInterface {
                public function linkToCatalog(\App\Stock\Domain\Entities\StockItem $stockItem): void {}
                public function attachCatalogData(iterable $stockItems): array { return is_array($stockItems) ? $stockItems : iterator_to_array($stockItems); }
                public function catalogItemExists(string $catalogItemId, ?string $origin = null): bool { return true; }
                public function getItem(string $itemId): ?array { return ['id' => $itemId, 'name' => 'Test Item', 'sku' => 'TEST-SKU']; }
                public function searchItems(string $searchTerm, int $limit = 50): array { return []; }
                public function getDefaultOriginName(): string { return 'test'; }
                public function registerOrigin(string $name, string $source, string $type = 'table'): string { return 'test'; }
            }
        );
    }

    public function test_can_create_pending_reservation()
    {
        // 1. Create Stock Item
        $itemResponse = $this->postJson('/api/v1/stock/items/create', [
            'item_id' => 'ITEM-001',
            'location_id' => 'LOC-1',
            'quantity' => 100,
            'expiration_date' => null,
            'lot_id' => 'LOT-001'
        ], ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);
        
        $stockItemId = $itemResponse['data']['id'];

        // 2. Request Pending Reservation
        $response = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => 'ITEM-001', // Note: Endpoint uses Catalog ID mapping? NO.
            // Wait, CreateReservationUseCase uses $request->itemId.
            // In StockApiTest, we create stock item with item_id='ITEM-001'.
            // And reserve using item_id='ITEM-001'.
            // Let's verify if itemId refers to StockItem ID or CatalogItem ID.
            // In CreateReservationRequest, it's just a string.
            // In Movement, it's itemId.
            // In StockMovementService:
            // $stockItem = $this->stockItemRepository->findByItemIdAndLocation($movement->getItemId(), ...)?
            // NO. $this->getOrCreateStockItem($movement).
            // It calls findByItemIdAndLocation.
            // MySQLStockItemRepository findByItemIdAndLocation($itemId, $locationId) uses 'item_id' column.
            // So if I create stock item with item_id='ITEM-001', I must reserve 'ITEM-001'.
            // BUT ReservationClient.ts sends `stockItemId` (UUID) as `item_id`.
            // In Frontend, stockItemId is the UUID of the stock_items row.
            // In Backend implementation of `create`, we pass `item_id`.
            // If the system uses UUIDs for stock items, we should pass UUID.
            // If it uses Catalog SKUs + Location, we pass Catalog SKU.
            // existing StockApiTest uses 'TEST-SKU'.
            // Frontend Client maps `stockItem.id` -> `stockItemId`.
            // So default vessel is likely tracking by UUID or keys?
            
            // Let's assume for this test we use 'ITEM-001' as identifier.
            'item_id' => 'ITEM-001',
            'location_id' => 'LOC-1',
            'quantity' => 10,
            'status' => 'pending',
            'reserved_by' => 'User Test'
        ], ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);

        $response->assertStatus(201);
        $reservationId = $response['reservation_id'];

        $this->assertDatabaseHas('stock_reservations', [
            'id' => $reservationId,
            'status' => 'pending',
            'quantity' => 10
        ]);

        // Verify NO stock movement (reserved_quantity should be 0 for this item if only pending)
        // Check stock item
        $stockResponse = $this->getJson("/api/v1/stock/items/show/{$stockItemId}", 
            ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);
        $stockResponse->assertJsonPath('data.reserved_quantity', 0);
        
        return $reservationId;
    }

    /**
     * @depends test_can_create_pending_reservation
     */
    public function test_approve_pending_reservation($reservationId)
    {
        // Re-create initial state because of RefreshDatabase? 
        // No, depends passes arg but DB is refreshed!
        // So I cannot use depends with RefreshDatabase efficiently without re-seeding.
        // I will merge tests.
    }
    
    public function test_approval_flow()
    {
        // 1. Create Stock Item
        $itemResponse = $this->postJson('/api/v1/stock/items/create', [
            'item_id' => 'ITEM-APPR',
            'location_id' => 'LOC-1',
            'quantity' => 100,
        ], ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);
        $stockItemUUID = $itemResponse['data']['id'];

        // 2. Create Pending Reservation
        $resResponse = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => 'ITEM-APPR',
            'location_id' => 'LOC-1',
            'quantity' => 20,
            'status' => 'pending',
            'reserved_by' => 'User Test'
        ], ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);

        $reservationId = $resResponse['reservation_id'];
        
        // Assert Pending
        $this->assertDatabaseHas('stock_reservations', [
            'id' => $reservationId,
            'status' => 'pending'
        ]);

        // Assert Stock Unchanged
        $this->getJson("/api/v1/stock/items/show/{$stockItemUUID}", ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm'])
             ->assertJsonPath('data.reserved_quantity', 0);

        // 3. Approve
        $approveResponse = $this->postJson("/api/v1/stock/reservations/{$reservationId}/approve", [], 
            ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);
        
        $approveResponse->assertStatus(200);
        
        // Assert Active
        $this->assertDatabaseHas('stock_reservations', [
            'id' => $reservationId,
            'status' => 'active'
        ]);

        // Assert Stock Reserved
        $this->getJson("/api/v1/stock/items/show/{$stockItemUUID}", ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm'])
             ->assertJsonPath('data.reserved_quantity', 20);
    }

    public function test_rejection_flow()
    {
         // 1. Create Stock Item
         $itemResponse = $this->postJson('/api/v1/stock/items/create', [
            'item_id' => 'ITEM-REJ',
            'location_id' => 'LOC-1',
            'quantity' => 50,
        ], ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);
        
        // 2. Pending
        $resResponse = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => 'ITEM-REJ',
            'location_id' => 'LOC-1',
            'quantity' => 10,
            'status' => 'pending',
        ], ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);
        
        $reservationId = $resResponse['reservation_id'];

        // 3. Reject
        $rejectResponse = $this->postJson("/api/v1/stock/reservations/{$reservationId}/reject", [], 
            ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm']);
        
        $rejectResponse->assertStatus(200);

        // Assert Rejected
        $this->assertDatabaseHas('stock_reservations', [
            'id' => $reservationId,
            'status' => 'rejected'
        ]);
        
        // Assert Stock 0
        $this->getJson("/api/v1/stock/items/show/{$itemResponse['data']['id']}", ['VESSEL-ACCESS-PRIVATE' => 'cFeSlpiSyAviOK7jk8FLbr3LBph5ypMOOcE5Xfhm'])
             ->assertJsonPath('data.reserved_quantity', 0);
    }
}
