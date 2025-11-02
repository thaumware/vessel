<?php

namespace App\Shared\Adapters\Portal;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * PORTAL - Conector simple para relaciones desde diferentes orígenes
 * 
 * Métodos:
 * - createOrigin() - Registrar un origen (tabla, URL)
 * - create() - Crear relación portal
 * - open() - Abrir origen y consultar
 * - attach() - Inyectar relaciones en modelos
 */
class Portal
{
    private $origin;
    private $query = [];

    private function __construct($originId)
    {
        $this->origin = DB::table('portal_origins')
            ->where('id', $originId)
            ->where('is_active', true)
            ->first();

        if (!$this->origin) {
            throw new \Exception("Portal origin '$originId' not found");
        }
    }

    /**
     * Crear un origen de datos
     */
    public static function createOrigin(
        string $direction,
        string $type = 'table',
        bool $active = true
    ): string {
        $id = Str::uuid();
        DB::table('portal_origins')->insert([
            'id' => $id,
            'direction' => $direction,
            'type' => $type,
            'is_active' => $active,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        return $id;
    }

    /**
     * Crear una relación portal
     */
    public static function createPortal(
        string $hasPortalId,
        string $hasPortalType,
        string $portalOriginId,
        string $externalId,
        ?array $metadata = null
    ): void {
        DB::table('portals')->insert([
            'id' => Str::uuid(),
            'has_portal_id' => $hasPortalId,
            'has_portal_type' => $hasPortalType,
            'portal_origin_id' => $portalOriginId,
            'external_id' => $externalId,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Abrir un origen de datos por ID
     */
    public static function open(string $originId): self
    {
        return new self($originId);
    }

    /**
     * Eliminar un origen de datos (soft delete)
     */
    public static function deleteOrigin(string $originId): void
    {
        DB::table('portal_origins')
            ->where('id', $originId)
            ->update(['deleted_at' => now()]);
    }

    /**
     * Establecer filtros/query
     */
    public function query(array $filters = []): self
    {
        $this->query = $filters;
        return $this;
    }

    /**
     * Obtener datos del origen
     */
    public function get(): array
    {
        if ($this->origin->type === 'table') {
            return $this->queryTable();
        }

        if ($this->origin->type === 'http') {
            return $this->queryHttp();
        }

        return [];
    }

    /**
     * Consultar tabla local
     */
    private function queryTable(): array
    {
        $q = DB::table($this->origin->direction);

        foreach ($this->query as $field => $value) {
            if ($field === 'ids') {
                $q->whereIn('id', (array) $value);
            } else {
                $q->where($field, $value);
            }
        }

        return $q->get()->map(fn($row) => (array) $row)->toArray();
    }

    /**
     * Consultar HTTP remoto
     */
    private function queryHttp(): array
    {
        try {
            $response = Http::get($this->origin->direction, $this->query);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Inyectar relaciones en array de modelos
     * 
     * @param $models Array o Collection de modelos
     */
    public static function attach($models): array|object
    {
        if (empty($models)) {
            return $models;
        }

        $isCollection = is_object($models) && method_exists($models, 'toArray');
        $collection = $isCollection ? $models : collect($models);
        $first = $collection->first();

        if (!$first) {
            return $models;
        }

        // Obtener IDs de modelos
        $modelIds = $collection->pluck('id')->toArray();

        // Buscar TODAS las relaciones de estos modelos
        $portals = DB::table('portals')
            ->whereIn('has_portal_id', $modelIds)
            ->get();

        if ($portals->isEmpty()) {
            return $models;
        }

        // Agrupar por origin_id para hacer queries eficientes
        $portalsByOrigin = $portals->groupBy('portal_origin_id');

        // Para cada origen, obtener sus datos
        $relatedDataByOrigin = [];
        foreach ($portalsByOrigin as $originId => $originPortals) {
            $externalIds = $originPortals->pluck('external_id')->toArray();
            $relatedDataByOrigin[$originId] = Portal::open($originId)
                ->query(['ids' => $externalIds])
                ->get();
        }

        // Inyectar en modelos
        $collection->each(function ($model) use ($portals, $relatedDataByOrigin) {
            $modelPortals = $portals->where('has_portal_id', $model->id);

            foreach ($modelPortals as $portal) {
                $originId = $portal->portal_origin_id;
                $externalId = $portal->external_id;

                $relatedData = $relatedDataByOrigin[$originId] ?? [];
                $relation = collect($relatedData)
                    ->filter(fn($item) => ($item['id'] ?? null) === $externalId)
                    ->first();

                if ($relation) {
                    $model->{"portal_" . $originId} = $relation;
                }
            }
        });

        return $isCollection ? $collection : $collection->toArray();
    }
}

