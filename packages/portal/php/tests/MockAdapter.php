<?php

namespace Thaumware\Portal\Tests;

use Thaumware\Portal\Contracts\StorageAdapter;
use Thaumware\Portal\Contracts\DataFetcher;

class MockAdapter implements StorageAdapter, DataFetcher
{
    private array $origins = [];
    private array $portals = [];
    private int $idCounter = 1;

    public function findOriginByName(string $name): ?array
    {
        foreach ($this->origins as $origin) {
            if ($origin['name'] === $name && !$origin['deleted_at']) {
                return $origin;
            }
        }
        return null;
    }

    public function findOriginById(string $id): ?array
    {
        foreach ($this->origins as $origin) {
            if ($origin['id'] === $id && $origin['is_active'] && !$origin['deleted_at']) {
                return $origin;
            }
        }
        return null;
    }

    public function createOrigin(string $name, string $source, string $type): string
    {
        $id = 'origin-' . $this->idCounter++;
        $this->origins[] = [
            'id' => $id,
            'name' => $name,
            'direction' => $source,
            'type' => $type,
            'is_active' => true,
            'deleted_at' => null,
        ];
        return $id;
    }

    public function createPortal(
        string $modelId,
        string $modelType,
        string $originId,
        string $relatedId,
        ?array $metadata
    ): void {
        $this->portals[] = [
            'id' => 'portal-' . $this->idCounter++,
            'model_id' => $modelId,
            'model_type' => $modelType,
            'origin_id' => $originId,
            'external_id' => $relatedId,
            'origin_name' => $this->findOriginById($originId)['name'] ?? null,
            'metadata' => $metadata,
        ];
    }

    public function findPortalsByModelIds(array $modelIds): array
    {
        $result = [];
        foreach ($this->portals as $portal) {
            if (in_array($portal['model_id'], $modelIds)) {
                $result[] = $portal;
            }
        }
        return $result;
    }

    public function fetch(array $origin, array $filters): array
    {
        // Simulate table data
        if ($origin['type'] === 'table') {
            $mockData = [
                ['id' => 'item-1', 'name' => 'Product A'],
                ['id' => 'item-2', 'name' => 'Product B'],
                ['id' => 'item-3', 'name' => 'Product C'],
            ];

            if (isset($filters['ids'])) {
                return array_filter($mockData, fn($item) => in_array($item['id'], $filters['ids']));
            }

            return $mockData;
        }

        return [];
    }

    public function deactivateOrigin(string $name): void
    {
        foreach ($this->origins as &$origin) {
            if ($origin['name'] === $name) {
                $origin['deleted_at'] = date('Y-m-d H:i:s');
            }
        }
    }
}
