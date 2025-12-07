<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\ValueObjects;

use App\Stock\Domain\ValueObjects\MovementType;
use PHPUnit\Framework\TestCase;

class MovementTypeTest extends TestCase
{
    public function test_inbound_types_add_stock(): void
    {
        $inbound = [
            MovementType::RECEIPT,
            MovementType::RETURN,
            MovementType::ADJUSTMENT_IN,
            MovementType::TRANSFER_IN,
            MovementType::PRODUCTION,
        ];

        foreach ($inbound as $type) {
            $this->assertTrue($type->addsStock(), "{$type->value} should add stock");
            $this->assertFalse($type->removesStock(), "{$type->value} should not remove stock");
            $this->assertEquals(1, $type->getQuantityMultiplier());
        }
    }

    public function test_outbound_types_remove_stock(): void
    {
        $outbound = [
            MovementType::SHIPMENT,
            MovementType::CONSUMPTION,
            MovementType::ADJUSTMENT_OUT,
            MovementType::TRANSFER_OUT,
            MovementType::DAMAGE,
            MovementType::EXPIRATION,
        ];

        foreach ($outbound as $type) {
            $this->assertTrue($type->removesStock(), "{$type->value} should remove stock");
            $this->assertFalse($type->addsStock(), "{$type->value} should not add stock");
            $this->assertEquals(-1, $type->getQuantityMultiplier());
        }
    }

    public function test_reservation_types(): void
    {
        $this->assertTrue(MovementType::RESERVE->affectsReservation());
        $this->assertTrue(MovementType::RESERVE->reserves());
        $this->assertFalse(MovementType::RESERVE->releases());
        $this->assertEquals(1, MovementType::RESERVE->getReservationMultiplier());

        $this->assertTrue(MovementType::RELEASE->affectsReservation());
        $this->assertTrue(MovementType::RELEASE->releases());
        $this->assertFalse(MovementType::RELEASE->reserves());
        $this->assertEquals(-1, MovementType::RELEASE->getReservationMultiplier());
    }

    public function test_neutral_types_do_not_affect_quantity(): void
    {
        $neutral = [MovementType::COUNT, MovementType::RELOCATION];

        foreach ($neutral as $type) {
            $this->assertFalse($type->addsStock());
            $this->assertFalse($type->removesStock());
            $this->assertFalse($type->affectsReservation());
            $this->assertEquals(0, $type->getQuantityMultiplier());
        }
    }

    public function test_transfer_types(): void
    {
        $this->assertTrue(MovementType::TRANSFER_IN->isTransfer());
        $this->assertTrue(MovementType::TRANSFER_OUT->isTransfer());
        $this->assertFalse(MovementType::RECEIPT->isTransfer());
    }

    public function test_labels(): void
    {
        $this->assertEquals('Recepción', MovementType::RECEIPT->label());
        $this->assertEquals('Envío', MovementType::SHIPMENT->label());
        $this->assertEquals('Reserva', MovementType::RESERVE->label());
        $this->assertEquals('Vencimiento', MovementType::EXPIRATION->label());
    }

    public function test_inbound_types_list(): void
    {
        $inbound = MovementType::inboundTypes();
        $this->assertContains(MovementType::RECEIPT, $inbound);
        $this->assertContains(MovementType::RETURN, $inbound);
        $this->assertNotContains(MovementType::SHIPMENT, $inbound);
    }

    public function test_outbound_types_list(): void
    {
        $outbound = MovementType::outboundTypes();
        $this->assertContains(MovementType::SHIPMENT, $outbound);
        $this->assertContains(MovementType::DAMAGE, $outbound);
        $this->assertNotContains(MovementType::RECEIPT, $outbound);
    }
}
