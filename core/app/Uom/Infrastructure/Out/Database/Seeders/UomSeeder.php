<?php

namespace App\Uom\Infrastructure\Out\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UomSeeder extends Seeder
{
    /**
     * Seed the database with base UoM data.
     * 
     * Uses INSERT IGNORE / ON CONFLICT DO NOTHING to prevent duplicates
     * when re-running the seeder.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedMeasures();
        $this->seedConversions();
    }

    private function seedCategories(): void
    {
        $categories = require __DIR__ . '/../../Data/categories.php';
        $now = now();
        $inserted = 0;

        foreach ($categories as $category) {
            $exists = DB::table('uom_categories')
                ->where('code', $category['code'])
                ->whereNull('workspace_id')
                ->exists();

            if (!$exists) {
                DB::table('uom_categories')->insert([
                    'id' => $category['id'],
                    'code' => $category['code'],
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'icon' => $category['icon'] ?? null,
                    'sort_order' => $category['sort_order'] ?? 0,
                    'workspace_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $inserted++;
            }
        }

        $this->command->info("✓ Categorías: {$inserted} insertadas, " . (count($categories) - $inserted) . " existentes");
    }

    private function seedMeasures(): void
    {
        $measures = require __DIR__ . '/../../Data/measures.php';
        $now = now();
        $inserted = 0;

        foreach ($measures as $measure) {
            $exists = DB::table('uom_measures')
                ->where('code', $measure['code'])
                ->whereNull('workspace_id')
                ->exists();

            if (!$exists) {
                DB::table('uom_measures')->insert([
                    'id' => $measure['id'],
                    'code' => $measure['code'],
                    'name' => $measure['name'],
                    'symbol' => $measure['symbol'] ?? null,
                    'category' => $measure['category'] ?? null,
                    'is_base' => $measure['is_base'] ?? false,
                    'description' => $measure['description'] ?? null,
                    'workspace_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $inserted++;
            }
        }

        $this->command->info("✓ Medidas: {$inserted} insertadas, " . (count($measures) - $inserted) . " existentes");
    }

    private function seedConversions(): void
    {
        $conversions = require __DIR__ . '/../../Data/conversions.php';
        $now = now();

        // Get measure ID mapping (code => uuid)
        $measureMap = DB::table('uom_measures')
            ->whereNull('workspace_id')
            ->pluck('id', 'code')
            ->toArray();

        $inserted = 0;
        $skipped = 0;

        foreach ($conversions as $conversion) {
            $fromCode = $conversion['from_measure_id'];
            $toCode = $conversion['to_measure_id'];

            if (!isset($measureMap[$fromCode]) || !isset($measureMap[$toCode])) {
                $skipped++;
                continue;
            }

            $fromId = $measureMap[$fromCode];
            $toId = $measureMap[$toCode];

            $exists = DB::table('uom_conversions')
                ->where('from_measure_id', $fromId)
                ->where('to_measure_id', $toId)
                ->whereNull('workspace_id')
                ->exists();

            if (!$exists) {
                DB::table('uom_conversions')->insert([
                    'id' => $conversion['id'],
                    'from_measure_id' => $fromId,
                    'to_measure_id' => $toId,
                    'factor' => $conversion['factor'],
                    'operation' => $conversion['operation'],
                    'workspace_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $inserted++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("✓ Conversiones: {$inserted} insertadas, {$skipped} existentes/omitidas");
    }
}
