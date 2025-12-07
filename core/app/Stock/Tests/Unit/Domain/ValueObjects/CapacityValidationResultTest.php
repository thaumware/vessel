<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Domain\ValueObjects;

use App\Stock\Domain\ValueObjects\CapacityValidationResult;
use PHPUnit\Framework\TestCase;

class CapacityValidationResultTest extends TestCase
{
    public function test_valid_result(): void
    {
        $result = CapacityValidationResult::valid();

        $this->assertTrue($result->isValid());
        $this->assertFalse($result->isInvalid());
        $this->assertNull($result->getErrorCode());
        $this->assertNull($result->getErrorMessage());
        $this->assertNull($result->getContext());
    }

    public function test_invalid_result(): void
    {
        $result = CapacityValidationResult::invalid(
            'TEST_ERROR',
            'Test error message',
            ['key' => 'value']
        );

        $this->assertFalse($result->isValid());
        $this->assertTrue($result->isInvalid());
        $this->assertEquals('TEST_ERROR', $result->getErrorCode());
        $this->assertEquals('Test error message', $result->getErrorMessage());
        $this->assertEquals(['key' => 'value'], $result->getContext());
    }

    public function test_exceeds_max_quantity(): void
    {
        $result = CapacityValidationResult::exceedsMaxQuantity(80, 30, 100, 'loc-1');

        $this->assertTrue($result->isInvalid());
        $this->assertEquals('EXCEEDS_MAX_QUANTITY', $result->getErrorCode());
        $this->assertStringContainsString('30', $result->getErrorMessage());
        $this->assertStringContainsString('100', $result->getErrorMessage());
        $this->assertEquals(80, $result->getContext()['current_quantity']);
        $this->assertEquals(30, $result->getContext()['requested_quantity']);
        $this->assertEquals(100, $result->getContext()['max_quantity']);
        $this->assertEquals('loc-1', $result->getContext()['location_id']);
        $this->assertEquals(110, $result->getContext()['would_be_total']);
    }

    public function test_exceeds_max_weight(): void
    {
        $result = CapacityValidationResult::exceedsMaxWeight(45.5, 10.5, 50.0, 'loc-1');

        $this->assertTrue($result->isInvalid());
        $this->assertEquals('EXCEEDS_MAX_WEIGHT', $result->getErrorCode());
        $this->assertEquals(45.5, $result->getContext()['current_weight']);
        $this->assertEquals(10.5, $result->getContext()['requested_weight']);
        $this->assertEquals(50.0, $result->getContext()['max_weight']);
    }

    public function test_item_type_not_allowed(): void
    {
        $result = CapacityValidationResult::itemTypeNotAllowed(
            'regular',
            ['hazmat', 'cold_chain'],
            'loc-1'
        );

        $this->assertTrue($result->isInvalid());
        $this->assertEquals('ITEM_TYPE_NOT_ALLOWED', $result->getErrorCode());
        $this->assertEquals('regular', $result->getContext()['item_type']);
        $this->assertEquals(['hazmat', 'cold_chain'], $result->getContext()['allowed_types']);
    }

    public function test_mixed_lots_not_allowed(): void
    {
        $result = CapacityValidationResult::mixedLotsNotAllowed('loc-1');

        $this->assertTrue($result->isInvalid());
        $this->assertEquals('MIXED_LOTS_NOT_ALLOWED', $result->getErrorCode());
        $this->assertEquals('loc-1', $result->getContext()['location_id']);
    }

    public function test_mixed_items_not_allowed(): void
    {
        $result = CapacityValidationResult::mixedItemsNotAllowed('loc-1');

        $this->assertTrue($result->isInvalid());
        $this->assertEquals('MIXED_ITEMS_NOT_ALLOWED', $result->getErrorCode());
        $this->assertEquals('loc-1', $result->getContext()['location_id']);
    }

    public function test_location_not_active(): void
    {
        $result = CapacityValidationResult::locationNotActive('loc-1');

        $this->assertTrue($result->isInvalid());
        $this->assertEquals('LOCATION_NOT_ACTIVE', $result->getErrorCode());
    }

    public function test_to_array_for_valid(): void
    {
        $result = CapacityValidationResult::valid();
        $array = $result->toArray();

        $this->assertTrue($array['is_valid']);
        $this->assertNull($array['error_code']);
        $this->assertNull($array['error_message']);
        $this->assertNull($array['context']);
    }

    public function test_to_array_for_invalid(): void
    {
        $result = CapacityValidationResult::exceedsMaxQuantity(80, 30, 100, 'loc-1');
        $array = $result->toArray();

        $this->assertFalse($array['is_valid']);
        $this->assertEquals('EXCEEDS_MAX_QUANTITY', $array['error_code']);
        $this->assertNotEmpty($array['error_message']);
        $this->assertIsArray($array['context']);
    }
}
