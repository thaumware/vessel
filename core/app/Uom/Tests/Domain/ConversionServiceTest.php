<?php

namespace App\Uom\Tests\Domain;

use App\Uom\Domain\Entities\Conversion;
use App\Uom\Domain\Entities\Measure;
use App\Uom\Domain\Services\ConversionService;
use App\Uom\Infrastructure\Out\InMemory\InMemoryConversionRepository;
use App\Uom\Infrastructure\Out\InMemory\InMemoryMeasureRepository;
use App\Uom\Tests\UomTestCase;

class ConversionServiceTest extends UomTestCase
{
    private ConversionService $service;
    private InMemoryMeasureRepository $measureRepository;
    private InMemoryConversionRepository $conversionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->measureRepository = new InMemoryMeasureRepository();
        $this->conversionRepository = new InMemoryConversionRepository();
        
        $this->service = new ConversionService(
            $this->conversionRepository,
            $this->measureRepository,
        );

        // Setup base measures for testing
        $this->setupTestMeasures();
    }

    private function setupTestMeasures(): void
    {
        // Mass measures
        $this->measureRepository->save(new Measure('kg', 'kg', 'Kilogram', 'kg', 'mass', true));
        $this->measureRepository->save(new Measure('g', 'g', 'Gram', 'g', 'mass', false));
        $this->measureRepository->save(new Measure('mg', 'mg', 'Milligram', 'mg', 'mass', false));
        $this->measureRepository->save(new Measure('lb', 'lb', 'Pound', 'lb', 'mass', false));

        // Length measures
        $this->measureRepository->save(new Measure('m', 'm', 'Meter', 'm', 'length', true));
        $this->measureRepository->save(new Measure('cm', 'cm', 'Centimeter', 'cm', 'length', false));
        $this->measureRepository->save(new Measure('km', 'km', 'Kilometer', 'km', 'length', false));

        // Conversions for mass
        $this->conversionRepository->save(new Conversion('g', 'kg', 0.001, 'mul')); // 1g = 0.001kg
        $this->conversionRepository->save(new Conversion('mg', 'g', 0.001, 'mul')); // 1mg = 0.001g
        $this->conversionRepository->save(new Conversion('lb', 'kg', 0.453592, 'mul')); // 1lb = 0.453592kg

        // Conversions for length
        $this->conversionRepository->save(new Conversion('cm', 'm', 0.01, 'mul')); // 1cm = 0.01m
        $this->conversionRepository->save(new Conversion('km', 'm', 1000, 'mul')); // 1km = 1000m
    }

    // === Basic Conversion Tests ===

    public function test_converts_same_measure_returns_original_value(): void
    {
        $result = $this->service->convert(100.0, 'kg', 'kg');
        $this->assertEquals(100.0, $result);
    }

    public function test_converts_direct_conversion(): void
    {
        // 1000g = 1kg
        $result = $this->service->convert(1000.0, 'g', 'kg');
        $this->assertEquals(1.0, $result);
    }

    public function test_converts_inverse_conversion(): void
    {
        // 1kg = 1000g (inverse of g->kg)
        $result = $this->service->convert(1.0, 'kg', 'g');
        $this->assertEquals(1000.0, $result);
    }

    public function test_converts_through_base_unit(): void
    {
        // mg -> g -> kg (chain conversion)
        // 1,000,000 mg = 1000g = 1kg
        $result = $this->service->convert(1000000.0, 'mg', 'kg');
        $this->assertEquals(1.0, $result);
    }

    public function test_converts_pounds_to_grams(): void
    {
        // 1lb -> kg -> g
        // 1lb = 0.453592kg = 453.592g
        $result = $this->service->convert(1.0, 'lb', 'g');
        $this->assertEqualsWithDelta(453.592, $result, 0.001);
    }

    // === Length Conversions ===

    public function test_converts_centimeters_to_meters(): void
    {
        // 100cm = 1m
        $result = $this->service->convert(100.0, 'cm', 'm');
        $this->assertEquals(1.0, $result);
    }

    public function test_converts_kilometers_to_centimeters(): void
    {
        // 1km = 1000m = 100000cm
        $result = $this->service->convert(1.0, 'km', 'cm');
        $this->assertEquals(100000.0, $result);
    }

    // === Error Handling ===

    public function test_throws_exception_for_cross_category_conversion(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No conversion path found');
        
        // kg (mass) cannot convert to m (length)
        $this->service->convert(1.0, 'kg', 'm');
    }

    public function test_throws_exception_for_unknown_measure(): void
    {
        $this->expectException(\RuntimeException::class);
        
        $this->service->convert(1.0, 'unknown', 'kg');
    }

    // === Helper Method Tests ===

    public function test_can_convert_returns_true_for_valid_path(): void
    {
        $this->assertTrue($this->service->canConvert('g', 'kg'));
        $this->assertTrue($this->service->canConvert('kg', 'g'));
        $this->assertTrue($this->service->canConvert('mg', 'kg'));
    }

    public function test_can_convert_returns_false_for_invalid_path(): void
    {
        $this->assertFalse($this->service->canConvert('kg', 'm'));
        $this->assertFalse($this->service->canConvert('unknown', 'kg'));
    }

    public function test_get_conversion_factor_returns_factor(): void
    {
        // g -> kg factor is 0.001
        $factor = $this->service->getConversionFactor('g', 'kg');
        $this->assertEquals(0.001, $factor);

        // kg -> g factor is 1000
        $factor = $this->service->getConversionFactor('kg', 'g');
        $this->assertEquals(1000.0, $factor);
    }

    public function test_get_conversion_factor_returns_null_for_invalid_path(): void
    {
        $factor = $this->service->getConversionFactor('kg', 'm');
        $this->assertNull($factor);
    }

    // === Precision Tests ===

    public function test_maintains_precision_for_decimal_conversions(): void
    {
        // 0.5g = 0.0005kg
        $result = $this->service->convert(0.5, 'g', 'kg');
        $this->assertEquals(0.0005, $result);
    }

    public function test_handles_very_small_values(): void
    {
        // 0.001mg = 0.000001g = 0.000000001kg
        $result = $this->service->convert(0.001, 'mg', 'kg');
        $this->assertEqualsWithDelta(0.000000001, $result, 0.0000000001);
    }

    public function test_handles_very_large_values(): void
    {
        // 1,000,000 kg = 1,000,000,000,000 mg
        $result = $this->service->convert(1000000.0, 'kg', 'mg');
        $this->assertEquals(1000000000000.0, $result);
    }
}
