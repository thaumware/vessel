<?php

namespace App\Stock\Tests\Domain;

use App\Stock\Domain\Entities\Batch;
use App\Stock\Tests\StockTestCase;

class BatchTest extends StockTestCase
{
    public function test_can_create_batch_with_all_fields(): void
    {
        $data = $this->createBatchData();

        $batch = new Batch(
            id: $data['id'],
            itemId: $data['sku'],
            locationId: $data['locationId'],
            quantity: $data['quantity'],
            lotNumber: $data['lotNumber']
        );

        $this->assertEquals($data['id'], $batch->id());
        $this->assertEquals($data['sku'], $batch->itemId());
        $this->assertEquals($data['locationId'], $batch->locationId());
        $this->assertEquals($data['quantity'], $batch->quantity());
        $this->assertEquals($data['lotNumber'], $batch->lotNumber());
    }

    public function test_can_create_batch_without_lot_number(): void
    {
        $batch = new Batch(
            id: $this->generateUuid(),
            itemId: 'SKU-001',
            locationId: $this->generateUuid(),
            quantity: 100
        );

        $this->assertNull($batch->lotNumber());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $data = $this->createBatchData();

        $batch = new Batch(
            id: $data['id'],
            itemId: $data['sku'],
            locationId: $data['locationId'],
            quantity: $data['quantity'],
            lotNumber: $data['lotNumber']
        );

        $array = $batch->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('sku', $array);
        $this->assertArrayHasKey('location_id', $array);
        $this->assertArrayHasKey('quantity', $array);
        $this->assertArrayHasKey('lot_number', $array);
    }

    public function test_to_array_uses_snake_case(): void
    {
        $batch = new Batch(
            id: $this->generateUuid(),
            itemId: 'SKU',
            locationId: $this->generateUuid(),
            quantity: 10,
            lotNumber: 'LOT-001'
        );

        $array = $batch->toArray();

        $this->assertArrayHasKey('location_id', $array);
        $this->assertArrayNotHasKey('locationId', $array);
        $this->assertArrayHasKey('lot_number', $array);
        $this->assertArrayNotHasKey('lotNumber', $array);
    }

    public function test_can_create_batch_with_zero_quantity(): void
    {
        $batch = new Batch(
            id: $this->generateUuid(),
            itemId: 'SKU-EMPTY',
            locationId: $this->generateUuid(),
            quantity: 0
        );

        $this->assertEquals(0, $batch->quantity());
    }
}
