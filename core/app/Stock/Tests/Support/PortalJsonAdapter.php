<?php

namespace App\Stock\Tests\Support;

use RuntimeException;
use Thaumware\Portal\Contracts\DataFetcher;
use Thaumware\Portal\Contracts\StorageAdapter;

/**
 * In-memory Portal adapter backed by a JSON fixture (portal_catalog.json).
 * Implements both StorageAdapter and DataFetcher so RelationLoader can resolve
 * origins and fetch related records without touching DB/HTTP.
 */
class PortalJsonAdapter implements StorageAdapter, DataFetcher
{
    private array $origins = [];
    private array $portals = [];
    private array $items = [];
    private array $uoms = [];
    private int $idCounter = 1;

    public function __construct(string $fixturePath, private ?string $workspaceId = null)
    {
        $json = @file_get_contents($fixturePath);
        if ($json === false) {
            throw new RuntimeException("Portal fixture not found: {$fixturePath}");
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->items = $data['items'] ?? [];
        $this->uoms = $data['uoms'] ?? [];
    }

    // StorageAdapter
    public function findOriginByName(string $name): ?array
    {
        foreach ($this->origins as $origin) {
            if ($origin['name'] === $name && $origin['deleted_at'] === null && $origin['is_active']) {
                return $origin;
            }
        }
        return null;
    }

    public function findOriginById(string $id): ?array
    {
        foreach ($this->origins as $origin) {
            if ($origin['id'] === $id && $origin['deleted_at'] === null && $origin['is_active']) {
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
            'workspace_id' => $this->workspaceId,
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
        $origin = $this->findOriginById($originId);
        $this->portals[] = [
            'id' => 'portal-' . $this->idCounter++,
            'model_id' => $modelId,
            'model_type' => $modelType,
            'origin_id' => $originId,
            'origin_name' => $origin['name'] ?? null,
            'external_id' => $relatedId,
            'metadata' => $metadata,
            'workspace_id' => $metadata['workspace_id'] ?? $this->workspaceId,
        ];
    }

    public function findPortalsByModelIds(array $modelIds): array
    {
        $results = [];
        foreach ($this->portals as $portal) {
            if (in_array($portal['model_id'], $modelIds, true)) {
                $origin = $this->findOriginById($portal['origin_id']);
                if (!$origin) {
                    continue;
                }
                if ($this->workspaceId !== null && $portal['workspace_id'] !== $this->workspaceId) {
                    continue;
                }
                $results[] = [
                    'model_id' => $portal['model_id'],
                    'external_id' => $portal['external_id'],
                    'origin_id' => $portal['origin_id'],
                    'origin_name' => $origin['name'],
                    'direction' => $origin['direction'],
                    'type' => $origin['type'],
                    'workspace_id' => $portal['workspace_id'] ?? null,
                ];
            }
        }
        return $results;
    }

    public function deactivateOrigin(string $name): void
    {
        foreach ($this->origins as &$origin) {
            if ($origin['name'] === $name) {
                $origin['deleted_at'] = date('Y-m-d H:i:s');
            }
        }
    }

    public function install(): void
    {
        // No-op for in-memory adapter.
    }

    // DataFetcher
    public function fetch(array $origin, array $filters): array
    {
        $ids = (array)($filters['ids'] ?? []);

        if ($origin['name'] === 'catalog_items') {
            return $this->pick($this->items, $ids);
        }

        if ($origin['name'] === 'uoms') {
            return $this->pick($this->uoms, $ids);
        }

        return [];
    }

    private function pick(array $source, array $ids): array
    {
        $rows = [];
        foreach ($ids as $id) {
            if (!isset($source[$id])) {
                continue;
            }
            $payload = $source[$id];
            if (!isset($payload['id'])) {
                $payload = array_merge(['id' => $id], $payload);
            }
            $rows[] = $payload;
        }
        return $rows;
    }
}
