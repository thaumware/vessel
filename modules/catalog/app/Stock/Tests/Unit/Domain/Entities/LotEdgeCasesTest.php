<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\Entities;

use App\Stock\Domain\Entities\Lot;
use App\Stock\Domain\ValueObjects\LotStatus;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;

/**
 * Tests de casos limite para la entidad Lot (refactorizada).
 * 
 * La nueva entidad Lot usa:
 * - itemId en lugar de sku
 * - identifiers[] array (lot_number, supplier_lot, batch_code, etc.)
 * - attributes[] array (expiration_date, production_date, reception_date, quality_grade, etc.)
 * - sourceType/sourceId en lugar de supplierId
 * - LotStatus enum en lugar de string status
 */
class LotEdgeCasesTest extends StockTestCase
{
    // === Casos limite de fechas de vencimiento ===

    public function test_lot_expires_exactly_at_midnight(): void
    {
        $today = (new DateTimeImmutable('today midnight'))->format('Y-m-d');
        
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001'],
            attributes: ['expiration_date' => $today]
        );

        // Un lote que vence hoy ya está vencido
        $this->assertTrue($lot->isExpired());
    }

    public function test_lot_not_expired_when_expiring_tomorrow(): void
    {
        $tomorrow = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
        
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001'],
            attributes: ['expiration_date' => $tomorrow]
        );

        // Vence mañana - aún no está vencido
        $this->assertFalse($lot->isExpired());
    }

    public function test_lot_without_expiration_never_expires(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001']
            // Sin attributes['expiration_date']
        );

        $this->assertFalse($lot->isExpired());
        $this->assertFalse($lot->isExpiringSoon(365)); // Ni en un año
        $this->assertNull($lot->daysUntilExpiration());
    }

    public function test_lot_expiring_in_30_days_is_expiring_soon(): void
    {
        $in30Days = (new DateTimeImmutable('+30 days'))->format('Y-m-d');
        
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001'],
            attributes: ['expiration_date' => $in30Days]
        );

        $this->assertTrue($lot->isExpiringSoon(30));
        $this->assertFalse($lot->isExpired());
    }

    public function test_expired_lot_shows_negative_days(): void
    {
        $yesterday = (new DateTimeImmutable('-1 day'))->format('Y-m-d');
        
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001'],
            attributes: ['expiration_date' => $yesterday]
        );

        $this->assertTrue($lot->isExpired());
        $this->assertLessThan(0, $lot->daysUntilExpiration());
    }

    // === Casos limite de estados ===

    public function test_depleted_lot_cannot_be_used(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            status: LotStatus::DEPLETED,
            identifiers: ['lot_number' => 'LOT-001']
        );

        $this->assertFalse($lot->isUsable());
        $this->assertFalse($lot->isActive());
    }

    public function test_quarantined_lot_cannot_be_used(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            status: LotStatus::QUARANTINE,
            identifiers: ['lot_number' => 'LOT-001']
        );

        $this->assertFalse($lot->isUsable());
        $this->assertTrue($lot->isInQuarantine());
    }

    public function test_active_but_expired_lot_cannot_be_used(): void
    {
        $yesterday = (new DateTimeImmutable('-1 day'))->format('Y-m-d');
        
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            status: LotStatus::ACTIVE,
            identifiers: ['lot_number' => 'LOT-001'],
            attributes: ['expiration_date' => $yesterday]
        );

        $this->assertFalse($lot->isUsable()); // Vencido aunque esté activo
        $this->assertTrue($lot->isActive());
        $this->assertTrue($lot->isExpired());
    }

    // === Transiciones de estado ===

    public function test_activate_returns_new_instance(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            status: LotStatus::QUARANTINE,
            identifiers: ['lot_number' => 'LOT-001']
        );

        $activated = $lot->activate();

        // Inmutabilidad: son objetos diferentes
        $this->assertNotSame($lot, $activated);
        $this->assertEquals(LotStatus::QUARANTINE, $lot->getStatus()); // Original sin cambios
        $this->assertEquals(LotStatus::ACTIVE, $activated->getStatus());
    }

    public function test_quarantine_preserves_all_data(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            status: LotStatus::ACTIVE,
            identifiers: ['lot_number' => 'LOT-001', 'supplier_lot' => 'SUP-LOT-001'],
            attributes: [
                'expiration_date' => (new DateTimeImmutable('+30 days'))->format('Y-m-d'),
                'production_date' => (new DateTimeImmutable('-10 days'))->format('Y-m-d'),
                'reception_date' => (new DateTimeImmutable('-5 days'))->format('Y-m-d')
            ],
            sourceType: 'supplier',
            sourceId: 'supplier-123',
            workspaceId: 'ws-123',
            meta: ['quality_score' => 95]
        );

        $quarantined = $lot->quarantine();

        $this->assertEquals('lot-1', $quarantined->getId());
        $this->assertEquals('LOT-001', $quarantined->getLotNumber());
        $this->assertEquals('PROD-001', $quarantined->getItemId());
        $this->assertEquals(LotStatus::QUARANTINE, $quarantined->getStatus());
        $this->assertEquals('ws-123', $quarantined->getWorkspaceId());
        $this->assertEquals(['quality_score' => 95], $quarantined->getMeta());
        $this->assertEquals('supplier', $quarantined->getSourceType());
        $this->assertEquals('supplier-123', $quarantined->getSourceId());
    }

    // === Identifiers flexibles ===

    public function test_multiple_identifiers(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: [
                'lot_number' => 'LOT-001',
                'supplier_lot' => 'SUP-LOT-XYZ',
                'batch_code' => 'BATCH-2024-001'
            ]
        );

        $this->assertEquals('LOT-001', $lot->getLotNumber());
        $this->assertEquals('SUP-LOT-XYZ', $lot->getIdentifier('supplier_lot'));
        $this->assertEquals('BATCH-2024-001', $lot->getIdentifier('batch_code'));
    }

    // === Attributes flexibles ===

    public function test_custom_attributes(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001'],
            attributes: [
                'quality_grade' => 'A',
                'temperature_zone' => 'cold',
                'organic_certified' => true
            ]
        );

        $this->assertEquals('A', $lot->getAttribute('quality_grade'));
        $this->assertEquals('cold', $lot->getAttribute('temperature_zone'));
        $this->assertTrue($lot->getAttribute('organic_certified'));
    }

    // === Source genérico ===

    public function test_source_type_and_id(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001'],
            sourceType: 'production_order',
            sourceId: 'PO-2024-001'
        );

        $this->assertEquals('production_order', $lot->getSourceType());
        $this->assertEquals('PO-2024-001', $lot->getSourceId());
    }

    // === Serialización ===

    public function test_to_array_includes_computed_fields(): void
    {
        $expDate = (new DateTimeImmutable('+15 days'))->format('Y-m-d');
        
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001'],
            attributes: ['expiration_date' => $expDate]
        );

        $array = $lot->toArray();

        $this->assertArrayHasKey('is_expired', $array);
        $this->assertArrayHasKey('is_expiring_soon', $array);
        $this->assertArrayHasKey('days_until_expiration', $array);
        
        $this->assertFalse($array['is_expired']);
        $this->assertTrue($array['is_expiring_soon']); // 15 días < 30 default
    }

    public function test_to_array_handles_null_dates(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-001']
        );

        $array = $lot->toArray();

        $this->assertNull($array['expiration_date']);
        $this->assertNull($array['days_until_expiration']);
        $this->assertFalse($array['is_expired']);
        $this->assertFalse($array['is_expiring_soon']);
    }
}
