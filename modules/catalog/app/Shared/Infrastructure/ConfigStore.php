<?php

namespace App\Shared\Infrastructure;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

/**
 * Simple key-value store backed by shared_config table.
 */
class ConfigStore
{
    private const TABLE = 'shared_config';

    public function get(string $key, $default = null)
    {
        if (!$this->tableReady()) {
            return $default;
        }

        try {
            $row = DB::table(self::TABLE)->where('key', $key)->first();
            if (!$row) {
                return $default;
            }

            $value = $row->value;
            if ($value === null) {
                return $default;
            }

            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        } catch (QueryException $e) {
            return $default;
        }
    }

    public function set(string $key, $value): void
    {
        if (!$this->tableReady()) {
            return;
        }

        $stored = is_scalar($value) ? (string) $value : json_encode($value);

        try {
            DB::table(self::TABLE)->updateOrInsert(
                ['key' => $key],
                ['value' => $stored, 'updated_at' => now(), 'created_at' => now()]
            );
        } catch (QueryException $e) {
            // Swallow if table missing; setup step will create it
        }
    }

    private function tableReady(): bool
    {
        try {
            return Schema::hasTable(self::TABLE);
        } catch (QueryException $e) {
            return false;
        }
    }

    public function all(): array
    {
        if (!$this->tableReady()) {
            return [];
        }

        try {
            $rows = DB::table(self::TABLE)->orderBy('key')->get();
            return $rows->map(function ($row) {
                $decoded = json_decode($row->value, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
                return ['key' => $row->key, 'value' => $value];
            })->toArray();
        } catch (QueryException $e) {
            return [];
        }
    }

    public function delete(string $key): void
    {
        if (!$this->tableReady()) {
            return;
        }

        try {
            DB::table(self::TABLE)->where('key', $key)->delete();
        } catch (QueryException $e) {
            // ignore
        }
    }
}
