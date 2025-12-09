<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Application;

use App\Stock\Application\UseCases\ValidateReservation\ReservationValidationRequest;
use App\Stock\Application\UseCases\ValidateReservation\ValidateReservationUseCase;
use App\Stock\Domain\Entities\LocationStockSettings;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ValidateReservationUseCaseTest extends TestCase
{
    private FakeStockItemRepository $stockRepo;
    private FakeSettingsRepository $settingsRepo;
    private FakeCatalogGateway $catalogGateway;
    private FakeLocationGateway $locationGateway;
    private ValidateReservationUseCase $useCase;

    protected function setUp(): void
    {
        $stockItem = new StockItem(
            id: 'stock-1',
            itemId: 'ITEM-1',
            locationId: 'LOC-1',
            quantity: 100,
            reservedQuantity: 20,
        );

        $this->stockRepo = new FakeStockItemRepository($stockItem);
        $this->settingsRepo = new FakeSettingsRepository(null);
        $this->catalogGateway = new FakeCatalogGateway(true);
        $this->locationGateway = new FakeLocationGateway(true);

        $this->useCase = new ValidateReservationUseCase(
            $this->stockRepo,
            $this->settingsRepo,
            $this->catalogGateway,
            $this->locationGateway
        );
    }

    public function test_denies_when_stock_insufficient_and_negative_not_allowed(): void
    {
        $request = new ReservationValidationRequest(
            itemId: 'ITEM-1',
            locationId: 'LOC-1',
            quantity: 90 // available = 80
        );

        $result = $this->useCase->execute($request);

        $this->assertFalse($result->canReserve);
        $this->assertNotEmpty($result->errors);
        $this->assertStringContainsString('insuficiente', strtolower($result->errors[0]));
    }

    public function test_allows_with_warning_when_negative_stock_enabled(): void
    {
        $settings = new LocationStockSettings(
            id: 'cfg-1',
            locationId: 'LOC-1',
            allowNegativeStock: true
        );
        $this->settingsRepo->settings = $settings;

        $request = new ReservationValidationRequest(
            itemId: 'ITEM-1',
            locationId: 'LOC-1',
            quantity: 120 // available 80 -> leaves negative
        );

        $result = $this->useCase->execute($request);

        $this->assertTrue($result->canReserve);
        $this->assertNotEmpty($result->warnings, 'Should warn when leaving negative stock');
        $this->assertEquals(80.0, $result->availableQuantity);
    }

    public function test_denies_when_exceeds_max_reservation_percentage(): void
    {
        // Max 50% of total (100) -> 50. Already reserved 20. Request 40 => 60 > 50.
        $settings = new LocationStockSettings(
            id: 'cfg-2',
            locationId: 'LOC-1',
            maxReservationPercentage: 50
        );
        $this->settingsRepo->settings = $settings;

        $request = new ReservationValidationRequest(
            itemId: 'ITEM-1',
            locationId: 'LOC-1',
            quantity: 40
        );

        $result = $this->useCase->execute($request);

        $this->assertFalse($result->canReserve);
        $this->assertNotEmpty($result->errors);
            $this->assertStringContainsStringIgnoringCase('límite', $result->errors[0]);
    }

    public function test_denies_when_item_not_found_in_catalog(): void
    {
        $this->catalogGateway->itemExists = false;

        $request = new ReservationValidationRequest(
            itemId: 'MISSING',
            locationId: 'LOC-1',
            quantity: 10
        );

        $result = $this->useCase->execute($request);

        $this->assertFalse($result->canReserve);
        $this->assertSame([], $result->warnings);
        $this->assertNotEmpty($result->errors);
    }
}

/**
 * Fakes below provide minimal behavior for the ValidateReservationUseCase dependencies.
 */
class FakeStockItemRepository implements StockItemRepositoryInterface
{
    public function __construct(private ?StockItem $item)
    {
    }

    public function findById(string $id): ?StockItem { return null; }
    public function findByItemId(string $itemId): array { return []; }
    public function findByItemAndLocation(string $itemId, string $locationId): ?StockItem { return $this->item; }
    public function findByLocation(string $locationId): array { return []; }
    public function findByCatalogItemId(string $catalogItemId, string $catalogOrigin): array { return []; }
    public function search(array $filters = [], int $limit = 50, int $offset = 0): array { return []; }
    public function save(StockItem $stockItem): StockItem { return $stockItem; }
    public function update(StockItem $stockItem): StockItem { return $stockItem; }
    public function delete(string $id): void {}
    public function adjustQuantity(string $itemId, string $locationId, int $delta): StockItem { return $this->item; }
    public function reserve(string $id, int $quantity): StockItem { return $this->item; }
    public function release(string $id, int $quantity): StockItem { return $this->item; }
    public function findWithCatalogItems(array $ids): array { return []; }
}

class FakeSettingsRepository implements LocationStockSettingsRepositoryInterface
{
    public function __construct(public ?LocationStockSettings $settings)
    {
    }

    public function findById(string $id): ?LocationStockSettings { return $this->settings; }
    public function findByLocationId(string $locationId): ?LocationStockSettings { return $this->settings; }
    public function findByLocationIds(array $locationIds): array { return $this->settings ? [$this->settings->getLocationId() => $this->settings] : []; }
    public function findAllActive(): array { return $this->settings ? [$this->settings] : []; }
    public function save(LocationStockSettings $settings): LocationStockSettings { $this->settings = $settings; return $settings; }
    public function delete(string $id): bool { $this->settings = null; return true; }
    public function existsForLocation(string $locationId): bool { return $this->settings !== null; }
}

class FakeCatalogGateway implements CatalogGatewayInterface
{
    public function __construct(public bool $itemExists)
    {
    }

    public function linkToCatalog(StockItem $stockItem): void {}
    public function attachCatalogData(iterable $stockItems): array { return []; }
    public function catalogItemExists(string $catalogItemId, ?string $origin = null): bool { return $this->itemExists; }
    public function getDefaultOriginName(): string { return 'internal'; }
    public function registerOrigin(string $name, string $source, string $type = 'table'): string { return $name; }

    // Extra helper used by ValidateReservationUseCase (not declared in interface)
    public function getItem(string $itemId): ?array
    {
        return $this->itemExists ? ['id' => $itemId, 'name' => 'Item '.$itemId] : null;
    }
}

class FakeLocationGateway implements LocationGatewayInterface
{
    public function __construct(public bool $exists)
    {
    }

    public function getDescendantIds(string $locationId): array { return []; }
    public function getChildrenIds(string $locationId): array { return []; }
    public function getParentId(string $locationId): ?string { return null; }
    public function getAncestorIds(string $locationId): array { return []; }
    public function exists(string $locationId): bool { return $this->exists; }
    public function getLocationType(string $locationId): ?string { return 'warehouse'; }

    // Extra helper used by ValidateReservationUseCase (not declared in interface)
    public function getLocation(string $locationId): ?array
    {
        return $this->exists ? ['id' => $locationId, 'name' => 'Loc '.$locationId] : null;
    }
}
