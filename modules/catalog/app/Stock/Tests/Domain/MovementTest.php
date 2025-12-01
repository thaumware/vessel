<?php

namespace App\Stock\Tests\Domain;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;

class MovementTest extends StockTestCase
{
    public function test_can_create_movement_with_all_fields(): void
    {
        $data = $this->createMovementData();

        $movement = new Movement(
            id: $data['id'],
            movementId: $data['movementId'],
            sku: $data['sku'],
            locationFromId: $data['locationFromId'],
            locationFromType: $data['locationFromType'],
            locationToId: $data['locationToId'],
            locationToType: $data['locationToType'],
            quantity: $data['quantity'],
            balanceAfter: $data['balanceAfter'],
            movementType: $data['movementType'],
            reference: $data['reference'],
            userId: $data['userId'],
            workspaceId: $data['workspaceId'],
            meta: $data['meta']
        );

        $this->assertEquals($data['id'], $movement->getId());
        $this->assertEquals($data['movementId'], $movement->getMovementId());
        $this->assertEquals($data['sku'], $movement->getSku());
        $this->assertEquals($data['locationFromId'], $movement->getLocationFromId());
        $this->assertEquals($data['locationFromType'], $movement->getLocationFromType());
        $this->assertEquals($data['locationToId'], $movement->getLocationToId());
        $this->assertEquals($data['locationToType'], $movement->getLocationToType());
        $this->assertEquals($data['quantity'], $movement->getQuantity());
        $this->assertEquals($data['balanceAfter'], $movement->getBalanceAfter());
        $this->assertEquals($data['movementType'], $movement->getMovementType());
        $this->assertEquals($data['reference'], $movement->getReference());
        $this->assertEquals($data['userId'], $movement->getUserId());
        $this->assertEquals($data['workspaceId'], $movement->getWorkspaceId());
    }

    public function test_can_create_incoming_movement(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            movementId: 'MOV-IN-001',
            sku: 'SKU-001',
            locationFromId: null,
            locationFromType: null,
            locationToId: $this->generateUuid(),
            locationToType: 'warehouse',
            quantity: 100,
            movementType: 'incoming'
        );

        $this->assertNull($movement->getLocationFromId());
        $this->assertNotNull($movement->getLocationToId());
        $this->assertEquals('incoming', $movement->getMovementType());
    }

    public function test_can_create_outgoing_movement(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            movementId: 'MOV-OUT-001',
            sku: 'SKU-001',
            locationFromId: $this->generateUuid(),
            locationFromType: 'warehouse',
            locationToId: null,
            locationToType: null,
            quantity: 50,
            movementType: 'outgoing'
        );

        $this->assertNotNull($movement->getLocationFromId());
        $this->assertNull($movement->getLocationToId());
        $this->assertEquals('outgoing', $movement->getMovementType());
    }

    public function test_can_create_transfer_movement(): void
    {
        $movement = new Movement(
            id: $this->generateUuid(),
            movementId: 'MOV-TRF-001',
            sku: 'SKU-001',
            locationFromId: $this->generateUuid(),
            locationFromType: 'warehouse',
            locationToId: $this->generateUuid(),
            locationToType: 'store',
            quantity: 25,
            movementType: 'transfer'
        );

        $this->assertNotNull($movement->getLocationFromId());
        $this->assertNotNull($movement->getLocationToId());
        $this->assertEquals('transfer', $movement->getMovementType());
    }

    public function test_timestamps_are_set_automatically(): void
    {
        $before = new DateTimeImmutable();

        $movement = new Movement(
            id: $this->generateUuid(),
            movementId: 'MOV-001',
            sku: 'SKU-001',
            locationFromId: null,
            locationFromType: null,
            locationToId: $this->generateUuid(),
            locationToType: 'warehouse',
            quantity: 10
        );

        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $movement->getCreatedAt());
        $this->assertLessThanOrEqual($after, $movement->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $movement->getUpdatedAt());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $data = $this->createMovementData();

        $movement = new Movement(
            id: $data['id'],
            movementId: $data['movementId'],
            sku: $data['sku'],
            locationFromId: $data['locationFromId'],
            locationFromType: $data['locationFromType'],
            locationToId: $data['locationToId'],
            locationToType: $data['locationToType'],
            quantity: $data['quantity'],
            balanceAfter: $data['balanceAfter'],
            movementType: $data['movementType'],
            reference: $data['reference'],
            userId: $data['userId'],
            workspaceId: $data['workspaceId']
        );

        $array = $movement->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('movement_id', $array);
        $this->assertArrayHasKey('sku', $array);
        $this->assertArrayHasKey('location_from_id', $array);
        $this->assertArrayHasKey('location_from_type', $array);
        $this->assertArrayHasKey('location_to_id', $array);
        $this->assertArrayHasKey('location_to_type', $array);
        $this->assertArrayHasKey('quantity', $array);
        $this->assertArrayHasKey('balance_after', $array);
        $this->assertArrayHasKey('movement_type', $array);
        $this->assertArrayHasKey('reference', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('workspace_id', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    public function test_can_store_meta_data(): void
    {
        $meta = ['source' => 'api', 'batch_id' => '123'];

        $movement = new Movement(
            id: $this->generateUuid(),
            movementId: 'MOV-001',
            sku: 'SKU-001',
            locationFromId: null,
            locationFromType: null,
            locationToId: $this->generateUuid(),
            locationToType: 'warehouse',
            quantity: 10,
            meta: $meta
        );

        $this->assertEquals($meta, $movement->getMeta());
    }
}
