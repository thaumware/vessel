<?php

declare(strict_types=1);

namespace App\Stock\Application\UseCases\ManageLocationSettings;

use App\Stock\Domain\Entities\LocationStockSettings;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use Ramsey\Uuid\Uuid;

/**
 * Use Case para crear o actualizar configuracion de capacidad de una ubicacion.
 */
class ManageLocationSettingsUseCase
{
    public function __construct(
        private LocationStockSettingsRepositoryInterface $settingsRepository,
        private LocationGatewayInterface $locationGateway,
    ) {}

    public function execute(ManageLocationSettingsInput $input): ManageLocationSettingsOutput
    {
        // Validar que la ubicacion exista
        if (!$this->locationGateway->exists($input->locationId)) {
            return ManageLocationSettingsOutput::error("Location '{$input->locationId}' does not exist");
        }

        // Buscar configuracion existente
        $existing = $this->settingsRepository->findByLocationId($input->locationId);

        if ($existing !== null) {
            // Actualizar existente
            $settings = new LocationStockSettings(
                id: $existing->getId(),
                locationId: $input->locationId,
                maxQuantity: $input->maxQuantity,
                maxWeight: $input->maxWeight,
                maxVolume: $input->maxVolume,
                allowedItemTypes: $input->allowedItemTypes,
                allowMixedLots: $input->allowMixedLots,
                allowMixedSkus: $input->allowMixedSkus,
                fifoEnforced: $input->fifoEnforced,
                isActive: $input->isActive,
                workspaceId: $input->workspaceId ?? $existing->getWorkspaceId(),
                meta: $input->meta ?? $existing->getMeta(),
                createdAt: $existing->getCreatedAt(),
            );
        } else {
            // Crear nueva
            $settings = new LocationStockSettings(
                id: Uuid::uuid4()->toString(),
                locationId: $input->locationId,
                maxQuantity: $input->maxQuantity,
                maxWeight: $input->maxWeight,
                maxVolume: $input->maxVolume,
                allowedItemTypes: $input->allowedItemTypes,
                allowMixedLots: $input->allowMixedLots,
                allowMixedSkus: $input->allowMixedSkus,
                fifoEnforced: $input->fifoEnforced,
                isActive: $input->isActive,
                workspaceId: $input->workspaceId,
                meta: $input->meta,
            );
        }

        $this->settingsRepository->save($settings);

        return ManageLocationSettingsOutput::success($settings);
    }
}
