<?php

namespace Thaumware\Portal\Adapters;

use Thaumware\Portal\Contracts\StorageAdapter;
use Thaumware\Portal\Contracts\DataFetcher;

/**
 * Laravel/Illuminate implementation
 * 
 * Install in Laravel app, not in portal package
 */
class IlluminateAdapter implements StorageAdapter, DataFetcher
{
    public function __construct(
        private object $db,
        private object $http,
        private object $str
    ) {}

    public function findOriginByName(string $name): ?array
    {
        $row = $this->db::table('portal_origins')
            ->where('name', $name)
            ->whereNull('deleted_at')
            ->first();

        return $row ? (array) $row : null;
    }

    public function findOriginById(string $id): ?array
    {
        $row = $this->db::table('portal_origins')
            ->where('id', $id)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        return $row ? (array) $row : null;
    }

    public function createOrigin(string $name, string $source, string $type): string
    {
        $id = $this->str::uuid()->toString();

        $this->db::table('portal_origins')->insert([
            'id' => $id,
            'name' => $name,
            'direction' => $source,
            'type' => $type,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $id;
    }

    public function createPortal(
        string $modelId,
        string $modelType,
        string $originId,
        string $relatedId,
        ?array $metadata
    ): void {
        $this->db::table('portals')->insert([
            'id' => $this->str::uuid()->toString(),
            'has_portal_id' => $modelId,
            'has_portal_type' => $modelType,
            'portal_origin_id' => $originId,
            'external_id' => $relatedId,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function findPortalsByModelIds(array $modelIds): array
    {
        return $this->db::table('portals as p')
            ->join('portal_origins as o', 'p.portal_origin_id', '=', 'o.id')
            ->whereIn('p.has_portal_id', $modelIds)
            ->whereNull('p.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.is_active', true)
            ->select(
                'p.has_portal_id as model_id',
                'p.external_id',
                'p.portal_origin_id as origin_id',
                'o.name as origin_name',
                'o.direction',
                'o.type'
            )
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();
    }

    public function fetch(array $origin, array $filters): array
    {
        if ($origin['type'] === 'table') {
            return $this->queryTable($origin['direction'], $filters);
        }

        if ($origin['type'] === 'http') {
            return $this->queryHttp($origin['direction'], $filters);
        }

        return [];
    }

    public function deactivateOrigin(string $name): void
    {
        $this->db::table('portal_origins')
            ->where('name', $name)
            ->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function queryTable(string $table, array $filters): array
    {
        $query = $this->db::table($table);

        foreach ($filters as $field => $value) {
            if ($field === 'ids') {
                $query->whereIn('id', (array) $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get()->map(fn($row) => (array) $row)->toArray();
    }

    private function queryHttp(string $url, array $filters): array
    {
        try {
            $response = $this->http::get($url, $filters);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
