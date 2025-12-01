<?php

namespace App\Uom\Tests\Application;

use App\Uom\Application\UseCases\ConvertMeasure;
use App\Uom\Domain\Entities\Conversion;
use App\Uom\Domain\Entities\Measure;
use App\Uom\Domain\Services\ConversionService;
use App\Uom\Infrastructure\Out\InMemory\InMemoryConversionRepository;
use App\Uom\Infrastructure\Out\InMemory\InMemoryMeasureRepository;
use App\Uom\Tests\UomTestCase;

class ConvertMeasureTest extends UomTestCase
{
    private ConvertMeasure $useCase;
    private InMemoryMeasureRepository $measureRepository;
    private InMemoryConversionRepository $conversionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->measureRepository = new InMemoryMeasureRepository();
        $this->conversionRepository = new InMemoryConversionRepository();
        
        $service = new ConversionService(
            $this->conversionRepository,
            $this->measureRepository,
        );
        
        $this->useCase = new ConvertMeasure($service);

        // Setup test data
        $this->setupTestData();
    }

    private function setupTestData(): void
    {
        // Measures
        $this->measureRepository->save(new Measure('kg', 'kg', 'Kilogram', 'kg', 'mass', true));
        $this->measureRepository->save(new Measure('g', 'g', 'Gram', 'g', 'mass', false));
        $this->measureRepository->save(new Measure('lb', 'lb', 'Pound', 'lb', 'mass', false));

        // Conversions
        $this->conversionRepository->save(new Conversion('g', 'kg', 0.001, 'mul'));
        $this->conversionRepository->save(new Conversion('lb', 'kg', 0.453592, 'mul'));
    }

    public function test_execute_returns_conversion_result_array(): void
    {
        $result = $this->useCase->execute(1000.0, 'g', 'kg');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('original_value', $result);
        $this->assertArrayHasKey('original_measure_id', $result);
        $this->assertArrayHasKey('converted_value', $result);
        $this->assertArrayHasKey('target_measure_id', $result);
        $this->assertArrayHasKey('conversion_factor', $result);
    }

    public function test_execute_converts_correctly(): void
    {
        $result = $this->useCase->execute(1000.0, 'g', 'kg');

        $this->assertEquals(1000.0, $result['original_value']);
        $this->assertEquals('g', $result['original_measure_id']);
        $this->assertEquals(1.0, $result['converted_value']);
        $this->assertEquals('kg', $result['target_measure_id']);
        $this->assertEquals(0.001, $result['conversion_factor']);
    }

    public function test_convert_value_returns_only_value(): void
    {
        $result = $this->useCase->convertValue(1000.0, 'g', 'kg');

        $this->assertIsFloat($result);
        $this->assertEquals(1.0, $result);
    }

    public function test_can_convert_returns_true_for_valid_conversion(): void
    {
        $this->assertTrue($this->useCase->canConvert('g', 'kg'));
    }

    public function test_can_convert_returns_false_for_invalid_conversion(): void
    {
        // No length measures defined, so this should fail
        $this->assertFalse($this->useCase->canConvert('g', 'unknown'));
    }

    public function test_same_measure_conversion(): void
    {
        $result = $this->useCase->execute(100.0, 'kg', 'kg');

        $this->assertEquals(100.0, $result['converted_value']);
        $this->assertEquals(1.0, $result['conversion_factor']);
    }

    public function test_pounds_to_kilograms(): void
    {
        $result = $this->useCase->execute(1.0, 'lb', 'kg');

        $this->assertEqualsWithDelta(0.453592, $result['converted_value'], 0.000001);
    }

    public function test_inverse_conversion(): void
    {
        // kg to g (inverse of g->kg)
        $result = $this->useCase->execute(1.0, 'kg', 'g');

        $this->assertEquals(1000.0, $result['converted_value']);
    }
}
