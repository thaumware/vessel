<?php

declare(strict_types=1);

namespace App\Stock\Tests\Feature;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Domain\ValueObjects\MovementStatus;
use App\Stock\Infrastructure\Handlers\CustomerLoanHandler;
use App\Stock\Infrastructure\Handlers\ConsignmentHandler;
use App\Stock\Tests\StockTestCase;
use App\Stock\Infrastructure\Out\InMemory\InMemoryMovementRepository;
use App\Stock\Infrastructure\Out\InMemory\InMemoryStockItemRepository;

/**
 * Test: Extensibilidad del sistema de movimientos con handlers custom.
 * 
 * Verifica que se pueden agregar tipos de movimiento personalizados
 * SIN modificar el enum MovementType.
 */
class CustomMovementHandlersTest extends StockTestCase
{
    private InMemoryMovementRepository $movementRepo;
    private InMemoryStockItemRepository $stockRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->movementRepo = new InMemoryMovementRepository();
        $this->stockRepo = new InMemoryStockItemRepository();
    }

    public function test_custom_loan_handler_reduces_stock(): void
    {
        // Arrange: Stock inicial
        $stockItem = new StockItem(
            id: 'stock-001',
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 100
        );
        $this->stockRepo->save($stockItem);

        // Movimiento custom: type=CUSTOM, referenceType='customer_loan'
        $movement = new Movement(
            id: 'mov-001',
            type: MovementType::CUSTOM, // ✅ Tipo genérico
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 5,
            referenceType: 'customer_loan', // ✅ Identifica el handler
            referenceId: 'LOAN-2024-001',
            meta: ['customer_id' => 'CUST-123']
        );

        // Service CON handler custom
        $service = new StockMovementService(
            movementRepository: $this->movementRepo,
            stockItemRepository: $this->stockRepo,
            customHandlers: [new CustomerLoanHandler()]
        );

        // Act
        $result = $service->process($movement);

        // Assert
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(95, $result->getStockItem()->getQuantity()); // 100 - 5
        $this->assertEquals(MovementStatus::COMPLETED, $result->getMovement()->getStatus());
    }

    public function test_custom_loan_return_adds_stock(): void
    {
        // Arrange: Stock inicial (después del préstamo)
        $stockItem = new StockItem(
            id: 'stock-001',
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 95
        );
        $this->stockRepo->save($stockItem);

        // Movimiento custom: 'loan_return'
        $movement = new Movement(
            id: 'mov-002',
            type: MovementType::CUSTOM,
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 5,
            referenceType: 'loan_return',
            referenceId: 'LOAN-2024-001',
            meta: ['returned_by' => 'CUST-123']
        );

        $service = new StockMovementService(
            movementRepository: $this->movementRepo,
            stockItemRepository: $this->stockRepo,
            customHandlers: [new CustomerLoanHandler()]
        );

        // Act
        $result = $service->process($movement);

        // Assert
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(100, $result->getStockItem()->getQuantity()); // 95 + 5 = vuelve a 100
    }

    public function test_custom_consignment_out_reduces_stock(): void
    {
        // Arrange
        $stockItem = new StockItem(
            id: 'stock-001',
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 100
        );
        $this->stockRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-003',
            type: MovementType::CUSTOM,
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 20,
            referenceType: 'consignment_out',
            referenceId: 'CONSIGN-2024-001',
            meta: ['consignee' => 'RETAIL-STORE-001']
        );

        $service = new StockMovementService(
            movementRepository: $this->movementRepo,
            stockItemRepository: $this->stockRepo,
            customHandlers: [new ConsignmentHandler()]
        );

        // Act
        $result = $service->process($movement);

        // Assert
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(80, $result->getStockItem()->getQuantity()); // 100 - 20
    }

    public function test_custom_consignment_return_adds_stock(): void
    {
        // Arrange
        $stockItem = new StockItem(
            id: 'stock-001',
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 80
        );
        $this->stockRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-004',
            type: MovementType::CUSTOM,
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 8,
            referenceType: 'consignment_return',
            referenceId: 'CONSIGN-2024-001',
            meta: ['sold' => false, 'reason' => 'No vendido']
        );

        $service = new StockMovementService(
            movementRepository: $this->movementRepo,
            stockItemRepository: $this->stockRepo,
            customHandlers: [new ConsignmentHandler()]
        );

        // Act
        $result = $service->process($movement);

        // Assert
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(88, $result->getStockItem()->getQuantity()); // 80 + 8
    }

    public function test_validation_fails_for_insufficient_stock_in_custom_handler(): void
    {
        // Arrange: Stock insuficiente
        $stockItem = new StockItem(
            id: 'stock-001',
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 2 // Solo 2 disponibles
        );
        $this->stockRepo->save($stockItem);

        $movement = new Movement(
            id: 'mov-005',
            type: MovementType::CUSTOM,
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 10, // Intentando prestar 10
            referenceType: 'customer_loan',
            referenceId: 'LOAN-2024-002',
            meta: ['customer_id' => 'CUST-456']
        );

        $service = new StockMovementService(
            movementRepository: $this->movementRepo,
            stockItemRepository: $this->stockRepo,
            customHandlers: [new CustomerLoanHandler()]
        );

        // Act
        $result = $service->process($movement);

        // Assert
        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('Stock insuficiente', $result->getErrors()[0]);
        $this->assertEquals(2, $this->stockRepo->findByItemAndLocation('ITEM-001', 'WAREHOUSE-MAIN')->getQuantity());
    }

    public function test_multiple_custom_handlers_can_coexist(): void
    {
        // Arrange: Registrar AMBOS handlers
        $service = new StockMovementService(
            movementRepository: $this->movementRepo,
            stockItemRepository: $this->stockRepo,
            customHandlers: [
                new CustomerLoanHandler(),
                new ConsignmentHandler()
            ]
        );

        $stockItem = new StockItem(
            id: 'stock-001',
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 100
        );
        $this->stockRepo->save($stockItem);

        // Act 1: Préstamo
        $loan = new Movement(
            id: 'mov-006',
            type: MovementType::CUSTOM,
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 10,
            referenceType: 'customer_loan',
            meta: ['customer_id' => 'CUST-789']
        );
        $result1 = $service->process($loan);

        // Act 2: Consignación
        $consignment = new Movement(
            id: 'mov-007',
            type: MovementType::CUSTOM,
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 15,
            referenceType: 'consignment_out',
            meta: ['consignee' => 'RETAIL-001']
        );
        $result2 = $service->process($consignment);

        // Assert
        $this->assertTrue($result1->isSuccess());
        $this->assertTrue($result2->isSuccess());
        
        $finalStock = $this->stockRepo->findByItemAndLocation('ITEM-001', 'WAREHOUSE-MAIN');
        $this->assertEquals(75, $finalStock->getQuantity()); // 100 - 10 (loan) - 15 (consignment)
    }

    public function test_standard_enum_types_still_work_with_custom_handlers_registered(): void
    {
        // Arrange: Service con handlers custom registrados
        $service = new StockMovementService(
            movementRepository: $this->movementRepo,
            stockItemRepository: $this->stockRepo,
            customHandlers: [new CustomerLoanHandler(), new ConsignmentHandler()]
        );

        $stockItem = new StockItem(
            id: 'stock-001',
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 100
        );
        $this->stockRepo->save($stockItem);

        // Act: Usar tipo ESTÁNDAR del enum
        $movement = new Movement(
            id: 'mov-008',
            type: MovementType::SHIPMENT, // ✅ Tipo estándar
            itemId: 'ITEM-001',
            locationId: 'WAREHOUSE-MAIN',
            quantity: 25
        );
        $result = $service->process($movement);

        // Assert: Debe funcionar normalmente (no usar handlers)
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(75, $result->getStockItem()->getQuantity());
    }
}
