<?php

declare(strict_types=1);

namespace App\Stock\Tests\Application\Movements;

use App\Stock\Application\UseCases\Movements\SearchMovements;
use App\Stock\Application\UseCases\Movements\ShowMovement;
use App\Stock\Application\UseCases\Movements\CreateMovement;
use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Domain\ValueObjects\MovementSearchCriteria;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Infrastructure\Out\InMemory\InMemoryMovementRepository;
use App\Stock\Infrastructure\Out\InMemory\InMemoryStockItemRepository;
use PHPUnit\Framework\TestCase;

class MovementUseCasesTest extends TestCase
{
    private InMemoryMovementRepository $movementRepository;

    protected function setUp(): void
    {
        $this->movementRepository = new InMemoryMovementRepository();
        $this->seedMovements();
    }

    private function seedMovements(): void
    {
        $movements = [
            new Movement(
                id: 'mov-001',
                type: MovementType::RECEIPT,
                itemId: 'ITEM-A',
                locationId: 'LOC-1',
                quantity: 100,
                status: MovementStatus::COMPLETED,
                lotId: 'LOT-001',
                referenceType: 'purchase_order',
                referenceId: 'PO-001',
                workspaceId: 'ws-1'
            ),
            new Movement(
                id: 'mov-002',
                type: MovementType::SHIPMENT,
                itemId: 'ITEM-A',
                locationId: 'LOC-1',
                quantity: 30,
                status: MovementStatus::COMPLETED,
                referenceType: 'sales_order',
                referenceId: 'SO-001',
                workspaceId: 'ws-1'
            ),
            new Movement(
                id: 'mov-003',
                type: MovementType::RECEIPT,
                itemId: 'ITEM-B',
                locationId: 'LOC-2',
                quantity: 50,
                status: MovementStatus::PENDING,
                lotId: 'LOT-002',
                workspaceId: 'ws-1'
            ),
            new Movement(
                id: 'mov-004',
                type: MovementType::TRANSFER_OUT,
                itemId: 'ITEM-A',
                locationId: 'LOC-1',
                quantity: 20,
                status: MovementStatus::COMPLETED,
                sourceLocationId: 'LOC-1',
                destinationLocationId: 'LOC-2',
                referenceType: 'transfer',
                referenceId: 'TR-001',
                workspaceId: 'ws-2'
            ),
        ];

        foreach ($movements as $movement) {
            $this->movementRepository->save($movement);
        }
    }

    // ========================================
    // SearchMovements Tests
    // ========================================

    public function test_search_all_movements(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria();

        $result = $useCase->execute($criteria);

        $this->assertCount(4, $result['data']);
        $this->assertEquals(4, $result['total']);
    }

    public function test_search_by_item_id(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(itemId: 'ITEM-A');

        $result = $useCase->execute($criteria);

        $this->assertCount(3, $result['data']);
        foreach ($result['data'] as $movement) {
            $this->assertEquals('ITEM-A', $movement->getItemId());
        }
    }

    public function test_search_by_location_id(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(locationId: 'LOC-1');

        $result = $useCase->execute($criteria);

        $this->assertCount(3, $result['data']);
    }

    public function test_search_by_type(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(type: MovementType::RECEIPT);

        $result = $useCase->execute($criteria);

        $this->assertCount(2, $result['data']);
    }

    public function test_search_by_status(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(status: MovementStatus::PENDING);

        $result = $useCase->execute($criteria);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('mov-003', $result['data'][0]->getId());
    }

    public function test_search_by_lot_id(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(lotId: 'LOT-001');

        $result = $useCase->execute($criteria);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('mov-001', $result['data'][0]->getId());
    }

    public function test_search_by_reference_id(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(referenceId: 'PO-001');

        $result = $useCase->execute($criteria);

        $this->assertCount(1, $result['data']);
    }

    public function test_search_by_workspace(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(workspaceId: 'ws-2');

        $result = $useCase->execute($criteria);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('mov-004', $result['data'][0]->getId());
    }

    public function test_search_with_multiple_filters(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(
            itemId: 'ITEM-A',
            type: MovementType::RECEIPT,
            status: MovementStatus::COMPLETED
        );

        $result = $useCase->execute($criteria);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('mov-001', $result['data'][0]->getId());
    }

    public function test_search_with_pagination(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(limit: 2);

        $result = $useCase->execute($criteria);

        $this->assertCount(2, $result['data']);
        $this->assertEquals(4, $result['total']); // Total sin paginar
        $this->assertEquals(2, $result['limit']);
    }

    public function test_search_with_offset(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = new MovementSearchCriteria(offset: 2, limit: 2);

        $result = $useCase->execute($criteria);

        $this->assertCount(2, $result['data']);
        $this->assertEquals(2, $result['offset']);
    }

    public function test_search_from_array(): void
    {
        $useCase = new SearchMovements($this->movementRepository);
        $criteria = MovementSearchCriteria::fromArray([
            'item_id' => 'ITEM-B',
            'status' => 'pending',
        ]);

        $result = $useCase->execute($criteria);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('ITEM-B', $result['data'][0]->getItemId());
    }

    // ========================================
    // ShowMovement Tests
    // ========================================

    public function test_show_movement_by_id(): void
    {
        $useCase = new ShowMovement($this->movementRepository);

        $result = $useCase->execute('mov-001');

        $this->assertNotNull($result);
        $this->assertEquals('mov-001', $result->getId());
        $this->assertEquals('ITEM-A', $result->getItemId());
    }

    public function test_show_movement_not_found(): void
    {
        $useCase = new ShowMovement($this->movementRepository);

        $result = $useCase->execute('nonexistent');

        $this->assertNull($result);
    }

    // ========================================
    // CreateMovement Tests
    // ========================================

    public function test_create_movement_processes_successfully(): void
    {
        $stockItemRepo = new InMemoryStockItemRepository();
        $service = new StockMovementService(
            $this->movementRepository,
            $stockItemRepo,
            allowNegativeStock: true
        );
        $useCase = new CreateMovement($service);

        $movement = new Movement(
            id: 'mov-new',
            type: MovementType::RECEIPT,
            itemId: 'NEW-ITEM',
            locationId: 'LOC-1',
            quantity: 50
        );

        $result = $useCase->execute($movement);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('NEW-ITEM', $result->getMovement()->getItemId());
    }
}
