<?php

declare(strict_types=1);

namespace App\Stock\Tests\Unit\Application;

use App\Stock\Application\UseCases\ManageLocationSettings\ManageLocationSettingsInput;
use App\Stock\Application\UseCases\ManageLocationSettings\ManageLocationSettingsOutput;
use App\Stock\Application\UseCases\ManageLocationSettings\ManageLocationSettingsUseCase;
use App\Stock\Domain\Entities\LocationStockSettings;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManageLocationSettingsUseCaseTest extends TestCase
{
    private ManageLocationSettingsUseCase $useCase;
    private MockObject&LocationStockSettingsRepositoryInterface $settingsRepository;
    private MockObject&LocationGatewayInterface $locationGateway;

    protected function setUp(): void
    {
        $this->settingsRepository = $this->createMock(LocationStockSettingsRepositoryInterface::class);
        $this->locationGateway = $this->createMock(LocationGatewayInterface::class);

        $this->useCase = new ManageLocationSettingsUseCase(
            $this->settingsRepository,
            $this->locationGateway
        );
    }

    public function test_creates_new_settings_for_valid_location(): void
    {
        $locationId = 'loc-1';

        $this->locationGateway
            ->method('exists')
            ->with($locationId)
            ->willReturn(true);

        $this->settingsRepository
            ->method('findByLocationId')
            ->with($locationId)
            ->willReturn(null);

        $this->settingsRepository
            ->expects($this->once())
            ->method('save');

        $input = new ManageLocationSettingsInput(
            locationId: $locationId,
            maxQuantity: 100,
            allowMixedSkus: false
        );

        $output = $this->useCase->execute($input);

        $this->assertTrue($output->success);
        $this->assertNotNull($output->settings);
        $this->assertEquals($locationId, $output->settings->getLocationId());
        $this->assertEquals(100, $output->settings->getMaxQuantity());
        $this->assertFalse($output->settings->allowsMixedSkus());
    }

    public function test_updates_existing_settings(): void
    {
        $locationId = 'loc-1';
        $existingSettings = LocationStockSettings::createWithCapacity(
            id: 'settings-1',
            locationId: $locationId,
            maxQuantity: 50
        );

        $this->locationGateway
            ->method('exists')
            ->willReturn(true);

        $this->settingsRepository
            ->method('findByLocationId')
            ->willReturn($existingSettings);

        $this->settingsRepository
            ->expects($this->once())
            ->method('save');

        $input = new ManageLocationSettingsInput(
            locationId: $locationId,
            maxQuantity: 200
        );

        $output = $this->useCase->execute($input);

        $this->assertTrue($output->success);
        $this->assertEquals('settings-1', $output->settings->getId()); // Mismo ID
        $this->assertEquals(200, $output->settings->getMaxQuantity()); // Nuevo valor
    }

    public function test_fails_for_nonexistent_location(): void
    {
        $locationId = 'nonexistent';

        $this->locationGateway
            ->method('exists')
            ->with($locationId)
            ->willReturn(false);

        $input = new ManageLocationSettingsInput(locationId: $locationId, maxQuantity: 100);
        $output = $this->useCase->execute($input);

        $this->assertFalse($output->success);
        $this->assertNull($output->settings);
        $this->assertStringContainsString('does not exist', $output->error);
    }

    public function test_input_from_array(): void
    {
        $data = [
            'location_id' => 'loc-1',
            'max_quantity' => 100,
            'max_weight' => 500.5,
            'allow_mixed_skus' => false,
            'fifo_enforced' => true,
        ];

        $input = ManageLocationSettingsInput::fromArray($data);

        $this->assertEquals('loc-1', $input->locationId);
        $this->assertEquals(100, $input->maxQuantity);
        $this->assertEquals(500.5, $input->maxWeight);
        $this->assertFalse($input->allowMixedSkus);
        $this->assertTrue($input->fifoEnforced);
    }

    public function test_output_to_array(): void
    {
        $settings = LocationStockSettings::createWithCapacity('id-1', 'loc-1', 100);
        $output = ManageLocationSettingsOutput::success($settings);

        $array = $output->toArray();

        $this->assertTrue($array['success']);
        $this->assertIsArray($array['settings']);
        $this->assertEquals('loc-1', $array['settings']['location_id']);
        $this->assertNull($array['error']);
    }

    public function test_error_output_to_array(): void
    {
        $output = ManageLocationSettingsOutput::error('Something went wrong');

        $array = $output->toArray();

        $this->assertFalse($array['success']);
        $this->assertNull($array['settings']);
        $this->assertEquals('Something went wrong', $array['error']);
    }
}
