<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Out\Gateways;

use App\Locations\Domain\Interfaces\LocationRepository;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;

/**
 * Adapter para que Stock pueda consultar el modulo Locations.
 * Implementa la interfaz del dominio de Stock usando el repositorio de Locations.
 */
class LocationsModuleGateway implements LocationGatewayInterface
{
    private LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @inheritDoc
     */
    public function getDescendantIds(string $locationId): array
    {
        $descendants = [];
        $visited = [];
        $this->collectDescendants($locationId, $descendants, $visited);

        return $descendants;
    }

    /**
     * @inheritDoc
     */
    public function getChildrenIds(string $locationId): array
    {
        $children = $this->locationRepository->findByFilters(['parent_id' => $locationId]);

        return array_map(fn ($loc) => $loc->getId(), $children);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(string $locationId): ?string
    {
        $location = $this->locationRepository->findById($locationId);

        return $location?->getParentId();
    }

    /**
     * @inheritDoc
     */
    public function getAncestorIds(string $locationId): array
    {
        $ancestors = [];
        $currentId = $locationId;

        while (true) {
            $parentId = $this->getParentId($currentId);

            if ($parentId === null) {
                break;
            }

            $ancestors[] = $parentId;
            $currentId = $parentId;
        }

        return $ancestors;
    }

    /**
     * @inheritDoc
     */
    public function exists(string $locationId): bool
    {
        return $this->locationRepository->findById($locationId) !== null;
    }

    /**
     * @inheritDoc
     */
    public function getLocationType(string $locationId): ?string
    {
        $location = $this->locationRepository->findById($locationId);

        return $location?->getType()->value;
    }

    /**
     * Devuelve datos básicos de la ubicación.
     */
    public function getLocation(string $locationId): ?array
    {
        $location = $this->locationRepository->findById($locationId);

        if ($location === null) {
            return null;
        }

        return [
            'id' => $location->getId(),
            'name' => method_exists($location, 'getName') ? $location->getName() : $location->getId(),
            'type' => $location->getType()->value ?? null,
        ];
    }

    /**
     * Recolecta recursivamente todos los descendientes de una ubicacion.
     *
     * @param string $locationId
     * @param string[] $descendants Array por referencia donde acumular IDs
     */
    private function collectDescendants(string $locationId, array &$descendants, array &$visited): void
    {
        if (in_array($locationId, $visited, true)) {
            return; // prevent cycles
        }
        $visited[] = $locationId;

        $childrenIds = $this->getChildrenIds($locationId);

        foreach ($childrenIds as $childId) {
            if (in_array($childId, $visited, true)) {
                continue;
            }
            $descendants[] = $childId;
            $this->collectDescendants($childId, $descendants, $visited);
        }
    }
}
