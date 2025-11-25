<?php

namespace App\Uom\Tests\Domain;

use App\Uom\Domain\Entities\Measure;
use App\Uom\Domain\Entities\Conversion;
use App\Uom\Tests\UomTestCase;
use InvalidArgumentException;

class MeasureTest extends UomTestCase
{
    public function test_can_create_measure(): void
    {
        $data = $this->createMeasureData();
        
        $measure = new Measure(
            id: $data['id'],
            code: $data['code'],
            name: $data['name'],
            description: $data['description'],
        );

        $this->assertEquals($data['id'], $measure->getId());
        $this->assertEquals($data['code'], $measure->getCode());
        $this->assertEquals($data['name'], $measure->getName());
        $this->assertEquals($data['description'], $measure->getDescription());
    }

    public function test_can_create_measure_without_description(): void
    {
        $measure = new Measure(
            id: $this->generateUuid(),
            code: 'KG',
            name: 'Kilogram',
        );

        $this->assertNull($measure->getDescription());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $measure = new Measure(
            id: 'meas-123',
            code: 'M',
            name: 'Meter',
            description: 'Unit of length',
        );

        $array = $measure->toArray();

        $this->assertEquals('meas-123', $array['id']);
        $this->assertEquals('M', $array['code']);
        $this->assertEquals('Meter', $array['name']);
        $this->assertEquals('Unit of length', $array['description']);
    }
}

class ConversionTest extends UomTestCase
{
    public function test_can_create_conversion(): void
    {
        $fromId = $this->generateUuid();
        $toId = $this->generateUuid();
        
        $conversion = new Conversion(
            fromMeasureId: $fromId,
            toMeasureId: $toId,
            factor: 1000.0,
            operation: 'mul',
        );

        $this->assertEquals($fromId, $conversion->getFromMeasureId());
        $this->assertEquals($toId, $conversion->getToMeasureId());
        $this->assertEquals(1000.0, $conversion->getFactor());
        $this->assertEquals('mul', $conversion->getOperation());
    }

    public function test_convert_with_multiplication(): void
    {
        $conversion = new Conversion(
            fromMeasureId: $this->generateUuid(),
            toMeasureId: $this->generateUuid(),
            factor: 1000.0,
            operation: 'mul',
        );

        // 5 kg * 1000 = 5000 g
        $this->assertEquals(5000.0, $conversion->convert(5.0));
    }

    public function test_convert_with_division(): void
    {
        $conversion = new Conversion(
            fromMeasureId: $this->generateUuid(),
            toMeasureId: $this->generateUuid(),
            factor: 1000.0,
            operation: 'div',
        );

        // 5000 g / 1000 = 5 kg
        $this->assertEquals(5.0, $conversion->convert(5000.0));
    }

    public function test_convert_with_addition(): void
    {
        $conversion = new Conversion(
            fromMeasureId: $this->generateUuid(),
            toMeasureId: $this->generateUuid(),
            factor: 273.15,
            operation: 'add',
        );

        // 0°C + 273.15 = 273.15 K
        $this->assertEquals(273.15, $conversion->convert(0.0));
    }

    public function test_convert_with_subtraction(): void
    {
        $conversion = new Conversion(
            fromMeasureId: $this->generateUuid(),
            toMeasureId: $this->generateUuid(),
            factor: 273.15,
            operation: 'sub',
        );

        // 273.15 K - 273.15 = 0°C
        $this->assertEquals(0.0, $conversion->convert(273.15));
    }

    public function test_convert_throws_on_invalid_operation(): void
    {
        $conversion = new Conversion(
            fromMeasureId: $this->generateUuid(),
            toMeasureId: $this->generateUuid(),
            factor: 1.0,
            operation: 'invalid',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operation: invalid');
        
        $conversion->convert(10.0);
    }

    public function test_to_array_uses_snake_case(): void
    {
        $conversion = new Conversion(
            fromMeasureId: 'from-123',
            toMeasureId: 'to-456',
            factor: 2.5,
            operation: 'mul',
        );

        $array = $conversion->toArray();

        $this->assertArrayHasKey('from_measure_id', $array);
        $this->assertArrayHasKey('to_measure_id', $array);
        $this->assertEquals('from-123', $array['from_measure_id']);
        $this->assertEquals('to-456', $array['to_measure_id']);
    }
}
