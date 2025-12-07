<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\Entities;

use App\Stock\Domain\Entities\Lot;
use App\Stock\Domain\ValueObjects\LotStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class LotTest extends TestCase
{
    public function test_create_lot(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            status: LotStatus::ACTIVE,
            identifiers: ['lot_number' => 'LOT-2024-001'],
            attributes: ['expiration_date' => (new DateTimeImmutable('+60 days'))->format('Y-m-d')]
        );

        $this->assertEquals('lot-1', $lot->getId());
        $this->assertEquals('PROD-001', $lot->getItemId());
        $this->assertEquals('LOT-2024-001', $lot->getLotNumber());
        $this->assertTrue($lot->hasExpiration());
        $this->assertFalse($lot->isExpired());
        $this->assertEquals(LotStatus::ACTIVE, $lot->getStatus());
    }

    public function test_lot_without_expiration(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-2024-001']
        );

        $this->assertFalse($lot->hasExpiration());
        $this->assertFalse($lot->isExpired());
        $this->assertNull($lot->daysUntilExpiration());
    }

    public function test_expired_lot(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-2024-001'],
            attributes: ['expiration_date' => (new DateTimeImmutable('-5 days'))->format('Y-m-d')]
        );

        $this->assertTrue($lot->isExpired());
        $this->assertFalse($lot->isUsable());
        $this->assertLessThan(0, $lot->daysUntilExpiration());
    }

    public function test_expiring_soon(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-2024-001'],
            attributes: ['expiration_date' => (new DateTimeImmutable('+15 days'))->format('Y-m-d')]
        );

        $this->assertTrue($lot->isExpiringSoon(30));
        $this->assertFalse($lot->isExpiringSoon(10));
        $this->assertFalse($lot->isExpired());
    }

    public function test_days_until_expiration(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-2024-001'],
            attributes: ['expiration_date' => (new DateTimeImmutable('+10 days'))->format('Y-m-d')]
        );

        $days = $lot->daysUntilExpiration();
        $this->assertGreaterThanOrEqual(9, $days);
        $this->assertLessThanOrEqual(10, $days);
    }

    public function test_status_active(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'SKU-001',
            identifiers: ['lot_number' => 'LOT-001']
        );

        $this->assertTrue($lot->isActive());
        $this->assertFalse($lot->isInQuarantine());
        $this->assertTrue($lot->isUsable());
    }

    public function test_status_quarantine(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'SKU-001',
            identifiers: ['lot_number' => 'LOT-001']
        );
        $quarantined = $lot->quarantine();

        $this->assertTrue($lot->isActive()); // Inmutable
        $this->assertTrue($quarantined->isInQuarantine());
        $this->assertFalse($quarantined->isUsable());
    }

    public function test_status_transitions(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'SKU-001',
            identifiers: ['lot_number' => 'LOT-001']
        );

        $expired = $lot->markAsExpired();
        $this->assertEquals(LotStatus::EXPIRED, $expired->getStatus());

        $depleted = $lot->markAsDepleted();
        $this->assertEquals(LotStatus::DEPLETED, $depleted->getStatus());

        $reactivated = $expired->activate();
        $this->assertTrue($reactivated->isActive());
    }

    public function test_lot_age(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'SKU-001',
            identifiers: ['lot_number' => 'LOT-001'],
            attributes: ['reception_date' => (new DateTimeImmutable('-10 days'))->format('Y-m-d')]
        );

        $age = $lot->getAgeInDays();
        $this->assertGreaterThanOrEqual(9, $age);
        $this->assertLessThanOrEqual(10, $age);
    }

    public function test_to_array(): void
    {
        $lot = new Lot(
            id: 'lot-1',
            itemId: 'PROD-001',
            identifiers: ['lot_number' => 'LOT-2024-001'],
            attributes: ['expiration_date' => (new DateTimeImmutable('+30 days'))->format('Y-m-d')],
            sourceType: 'supplier',
            sourceId: 'sup-1'
        );

        $array = $lot->toArray();

        $this->assertEquals('lot-1', $array['id']);
        $this->assertEquals('LOT-2024-001', $array['lot_number']);
        $this->assertEquals('PROD-001', $array['item_id']);
        $this->assertEquals('active', $array['status']);
        $this->assertFalse($array['is_expired']);
        $this->assertIsInt($array['days_until_expiration']);
        $this->assertArrayHasKey('expiration_date', $array);
    }
}
