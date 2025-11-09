<?php

namespace Thaumware\Portal\Core;

use Thaumware\Portal\Contracts\StorageAdapter;
use Thaumware\Portal\Contracts\DataFetcher;

/**
 * Loads relations in batch
 */
class RelationLoader
{
    public function __construct(
        private StorageAdapter $storage,
        private DataFetcher $fetcher
    ) {}

    public function attach($models)
    {
        if (empty($models)) {
            return $models;
        }

        $isCollection = is_object($models) && method_exists($models, 'toArray');
        $collection = $isCollection ? $models : $models;

        $isEmpty = is_array($collection) ? empty($collection) : (method_exists($collection, 'isEmpty') && $collection->isEmpty());
        if ($isEmpty) {
            return $models;
        }

        // Get model IDs
        $modelIds = is_array($collection) 
            ? array_column($collection, 'id')
            : (method_exists($collection, 'pluck') ? $collection->pluck('id')->toArray() : []);

        // Fetch all portals for these models
        $portals = $this->storage->findPortalsByModelIds($modelIds);

        if (empty($portals)) {
            return $models;
        }

        // Group by origin_id
        $portalsByOrigin = $this->groupBy($portals, 'origin_id');

        // Fetch related data per origin (batch)
        $relatedDataByOrigin = [];
        foreach ($portalsByOrigin as $originId => $originPortals) {
            $externalIds = array_column($originPortals, 'external_id');
            $origin = $this->storage->findOriginById($originId);

            if (!$origin) continue;

            $relatedDataByOrigin[$originId] = $this->fetcher->fetch(
                $origin,
                ['ids' => $externalIds]
            );
        }

        // Inject into models
        foreach ($collection as $model) {
            $modelPortals = array_filter($portals, fn($p) => $p['model_id'] === $model->id);

            foreach ($modelPortals as $portal) {
                $originId = $portal['origin_id'];
                $externalId = $portal['external_id'];
                $originName = $portal['origin_name'];

                $relatedData = $relatedDataByOrigin[$originId] ?? [];
                $relation = $this->findById($relatedData, $externalId);

                if ($relation) {
                    $model->{"portal_{$originName}"} = $relation;
                }
            }
        }

        return $isCollection ? $collection : $this->toArray($collection);
    }

    private function groupBy(array $items, string $key): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[$item[$key]][] = $item;
        }
        return $result;
    }

    private function findById(array $items, string $id): ?array
    {
        foreach ($items as $item) {
            if (($item['id'] ?? null) === $id) {
                return $item;
            }
        }
        return null;
    }

    private function toArray($collection): array
    {
        return is_array($collection) ? $collection : iterator_to_array($collection);
    }
}
