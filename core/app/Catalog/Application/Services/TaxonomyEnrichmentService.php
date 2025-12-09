<?php

namespace App\Catalog\Application\Services;

use Illuminate\Support\Facades\DB;

/**
 * Servicio para enriquecer items con información de taxonomía.
 */
class TaxonomyEnrichmentService
{
    /**
     * Enriquece items con nombres de términos de taxonomía.
     * 
     * @param array $items Array de items del catálogo
     * @return array Items enriquecidos con 'terms' (nombre y datos del término)
     */
    public function enrichWithTerms(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        // Extraer todos los term_ids de los items
        $allTermIds = [];
        foreach ($items as $item) {
            if (!empty($item['term_ids'])) {
                $allTermIds = array_merge($allTermIds, $item['term_ids']);
            }
        }
        $allTermIds = array_unique($allTermIds);

        if (empty($allTermIds)) {
            // Si no hay términos, devolver items sin cambios
            return array_map(function ($item) {
                $item['terms'] = [];
                return $item;
            }, $items);
        }

        // Buscar términos en la tabla taxonomy_terms
        $terms = DB::table('taxonomy_terms')
            ->whereIn('id', $allTermIds)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id')
            ->map(fn($term) => [
                'id' => $term->id,
                'name' => $term->name,
                'slug' => $term->slug ?? null,
                'vocabulary_id' => $term->vocabulary_id ?? null,
            ])
            ->toArray();

        // Enriquecer cada item
        return array_map(function ($item) use ($terms) {
            if (!empty($item['term_ids'])) {
                $item['terms'] = array_values(array_filter(
                    array_map(
                        fn($termId) => $terms[$termId] ?? null,
                        $item['term_ids']
                    )
                ));
            } else {
                $item['terms'] = [];
            }
            return $item;
        }, $items);
    }

    /**
     * Filtra items por term_id.
     * 
     * @param array $items Items del catálogo
     * @param string $termId ID del término
     * @return array Items filtrados
     */
    public function filterByTerm(array $items, string $termId): array
    {
        // Obtener items que tienen relación con este término
        $itemIds = DB::table('catalog_item_terms')
            ->where('term_id', $termId)
            ->pluck('item_id')
            ->toArray();

        if (empty($itemIds)) {
            return [];
        }

        return array_filter($items, fn($item) => in_array($item['id'], $itemIds));
    }
}
