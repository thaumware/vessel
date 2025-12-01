<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\Entities;

use App\Stock\Domain\Entities\Lot;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class LotTest extends TestCase
{
    public function test_create_lot(): void
    {
        $lot = Lot::create(
            id: 'lot-1',
            lotNumber: 'LOT-2024-001',
            sku: 'PROD-001',
            expirationDate: new DateTimeImmutable('+60 days')
        );

        $this->assertEquals('lot-1', $lot->getId());
        $this->assertEquals('LOT-2024-001', $lot->getLotNumber());
        $this->assertEquals('PROD-001', $lot->getSku());
        $this->assertTrue($lot->hasExpiration());
        $this->assertFalse($lot->isExpired());
        $this->assertEquals('active', $lot->getStatus());
    }

    public function test_lot_without_expiration(): void
    {
        $lot = Lot::create(
            id: 'lot-1',
            lotNumber: 'LOT-2024-001',
            sku: 'PROD-001'
        );

        $this->assertFalse($lot->hasExpiration());
        $this->assertFalse($lot->isExpired());
        $this->assertNull($lot->daysUntilExpiration());
    }

    public function test_expired_lot(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            lotNumber: 'LOT-2024-001',
            sku: 'PROD-001',
            expirationDate: new DateTimeImmutable('-5 days')
        );

        $this->assertTrue($lot->isExpired());
        $this->assertFalse($lot->isUsable());
        $this->assertLessThan(0, $lot->daysUntilExpiration());
    }

    public function test_expiring_soon(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            lotNumber: 'LOT-2024-001',
            sku: 'PROD-001',
            expirationDate: new DateTimeImmutable('+15 days')
        );

        $this->assertTrue($lot->isExpiringSoon(30));
        $this->assertFalse($lot->isExpiringSoon(10));
        $this->assertFalse($lot->isExpired());
    }

    public function test_days_until_expiration(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            lotNumber: 'LOT-2024-001',
            sku: 'PROD-001',
            expirationDate: new DateTimeImmutable('+10 days')
        );

        $days = $lot->daysUntilExpiration();
        $this->assertGreaterThanOrEqual(9, $days);
        $this->assertLessThanOrEqual(10, $days);
    }

    public function test_status_active(): void
    {
        $lot = Lot::create('lot-1', 'LOT-001', 'SKU-001');

        $this->assertTrue($lot->isActive());
        $this->assertFalse($lot->isInQuarantine());
        $this->assertTrue($lot->isUsable());
    }

    public function test_status_quarantine(): void
    {
        $lot = Lot::create('lot-1', 'LOT-001', 'SKU-001');
        $quarantined = $lot->quarantine();

        $this->assertTrue($lot->isActive()); // Inmutable
        $this->assertTrue($quarantined->isInQuarantine());
        $this->assertFalse($quarantined->isUsable());
    }

    public function test_status_transitions(): void
    {
        $lot = Lot::create('lot-1', 'LOT-001', 'SKU-001');

        $expired = $lot->markAsExpired();
        $this->assertEquals('expired', $expired->getStatus());

        $depleted = $lot->markAsDepleted();
        $this->assertEquals('depleted', $depleted->getStatus());

        $reactivated = $expired->activate();
        $this->assertTrue($reactivated->isActive());
    }

    public function test_lot_age(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            lotNumber: 'LOT-001',
            sku: 'SKU-001',
            receptionDate: new DateTimeImmutable('-10 days')
        );

        $age = $lot->getAgeInDays();
        $this->assertGreaterThanOrEqual(9, $age);
        $this->assertLessThanOrEqual(10, $age);
    }

    public function test_to_array(): void
    {
        $lot = Lot::create(
            id: 'lot-1',
            lotNumber: 'LOT-2024-001',
            sku: 'PROD-001',
            expirationDate: new DateTimeImmutable('+30 days'),
            supplierId: 'sup-1'
        );

        $array = $lot->toArray();

        $this->assertEquals('lot-1', $array['id']);
        $this->assertEquals('LOT-2024-001', $array['lot_number']);
        $this->assertEquals('PROD-001', $array['sku']);
        $this->assertEquals('sup-1', $array['supplier_id']);
        $this->assertEquals('active', $array['status']);
        $this->assertFalse($array['is_expired']);
        $this->assertIsInt($array['days_until_expiration']);
        $this->assertArrayHasKey('expiration_date', $array);
    }
}
