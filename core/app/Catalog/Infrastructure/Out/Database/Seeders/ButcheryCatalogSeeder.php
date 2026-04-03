<?php

namespace App\Catalog\Infrastructure\Out\Database\Seeders;

use App\Uom\Infrastructure\Out\Database\Seeders\UomSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ButcheryCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $data = require __DIR__ . '/../../Data/butchery_catalog.php';
        $now = now();

        $this->seedBaseUomIfAvailable();
        $uomMap = $this->resolveUomMap();

        $vocabularyIds = [];
        foreach ($data['vocabularies'] as $key => $vocabulary) {
            $vocabularyIds[$key] = $this->firstOrCreateVocabulary($vocabulary, $now);
        }

        $termIds = [];
        foreach ($data['terms'] as $group => $terms) {
            foreach ($terms as $slug => $term) {
                $termIds[$group][$slug] = $this->firstOrCreateTerm(
                    vocabularyId: $vocabularyIds[$group],
                    slug: $slug,
                    name: $term['name'],
                    description: $term['description'] ?? null,
                    now: $now,
                );
            }
        }

        $insertedItems = 0;
        $attachedTerms = 0;

        foreach ($data['items'] as $item) {
            $itemId = $this->firstOrCreateItem(
                item: $item,
                uomId: $uomMap[$item['uom_code'] ?? 'kg'] ?? null,
                now: $now,
                insertedItems: $insertedItems,
            );

            $attachedTerms += $this->attachTerm(
                itemId: $itemId,
                termId: $termIds['species'][$item['species']],
                now: $now,
            );

            $attachedTerms += $this->attachTerm(
                itemId: $itemId,
                termId: $termIds['cut_types'][$item['cut_type']],
                now: $now,
            );
        }

        $this->command?->info(sprintf(
            'Butchery catalog: %d items nuevos, %d relaciones item-term nuevas.',
            $insertedItems,
            $attachedTerms
        ));
    }

    private function resolveUomMap(): array
    {
        if (!Schema::hasTable('uom_measures')) {
            return [];
        }

        return DB::table('uom_measures')
            ->whereIn('code', ['kg', 'unit'])
            ->whereNull('workspace_id')
            ->pluck('id', 'code')
            ->toArray();
    }

    private function seedBaseUomIfAvailable(): void
    {
        if (
            !Schema::hasTable('uom_categories') ||
            !Schema::hasTable('uom_measures') ||
            !Schema::hasTable('uom_conversions')
        ) {
            return;
        }

        $this->call(UomSeeder::class);
    }

    private function firstOrCreateVocabulary(array $vocabulary, $now): string
    {
        $existingId = DB::table('taxonomy_vocabularies')
            ->where('slug', $vocabulary['slug'])
            ->whereNull('workspace_id')
            ->value('id');

        if ($existingId !== null) {
            return $existingId;
        }

        $id = (string) Str::uuid();

        DB::table('taxonomy_vocabularies')->insert([
            'id' => $id,
            'name' => $vocabulary['name'],
            'slug' => $vocabulary['slug'],
            'description' => $vocabulary['description'] ?? null,
            'workspace_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $id;
    }

    private function firstOrCreateTerm(
        string $vocabularyId,
        string $slug,
        string $name,
        ?string $description,
        $now
    ): string {
        $existingId = DB::table('catalog_terms')
            ->where('slug', $slug)
            ->where('vocabulary_id', $vocabularyId)
            ->whereNull('workspace_id')
            ->value('id');

        if ($existingId !== null) {
            return $existingId;
        }

        $id = (string) Str::uuid();

        DB::table('catalog_terms')->insert([
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'vocabulary_id' => $vocabularyId,
            'workspace_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $id;
    }

    private function firstOrCreateItem(array $item, ?string $uomId, $now, int &$insertedItems): string
    {
        $existingItem = DB::table('catalog_items')
            ->where('name', $item['name'])
            ->whereNull('workspace_id')
            ->first(['id', 'uom_id']);

        if ($existingItem !== null) {
            DB::table('catalog_items')
                ->where('id', $existingItem->id)
                ->update([
                    'description' => $this->buildDescription($item),
                    'uom_id' => $uomId ?? $existingItem->uom_id,
                    'notes' => $this->buildNotes($item),
                    'status' => 'active',
                    'updated_at' => $now,
                ]);

            return $existingItem->id;
        }

        $id = (string) Str::uuid();

        DB::table('catalog_items')->insert([
            'id' => $id,
            'name' => $item['name'],
            'description' => $this->buildDescription($item),
            'uom_id' => $uomId,
            'notes' => $this->buildNotes($item),
            'status' => 'active',
            'workspace_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $insertedItems++;

        return $id;
    }

    private function attachTerm(string $itemId, string $termId, $now): int
    {
        $exists = DB::table('catalog_item_terms')
            ->where('item_id', $itemId)
            ->where('term_id', $termId)
            ->exists();

        if ($exists) {
            return 0;
        }

        DB::table('catalog_item_terms')->insert([
            'item_id' => $itemId,
            'term_id' => $termId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return 1;
    }

    private function buildDescription(array $item): string
    {
        $speciesLabels = [
            'cerdo' => 'cerdo',
            'pollo' => 'pollo',
            'vacuno' => 'vacuno',
        ];

        $typeLabels = [
            'corte-directo' => 'corte directo',
            'desposte-fino' => 'desposte fino',
            'subproducto' => 'subproducto',
            'elaborado' => 'elaborado',
        ];

        return sprintf(
            'Item demo de carniceria para %s. Clasificacion: %s.',
            $speciesLabels[$item['species']] ?? $item['species'],
            $typeLabels[$item['cut_type']] ?? $item['cut_type']
        );
    }

    private function buildNotes(array $item): string
    {
        return sprintf(
            'Seeder carniceria demo | especie=%s | tipo=%s',
            $item['species'],
            $item['cut_type']
        );
    }
}
