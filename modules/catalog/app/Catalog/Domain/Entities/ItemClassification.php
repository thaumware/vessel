<?php

namespace App\Catalog\Domain\Entities;

/**
 * ItemClassification representa la asignación de un item a un término de taxonomía.
 * Mantiene solo la mínima información (itemId, vocabularyId, termId) y un path opcional
 * para soportar jerarquías sin acoplar la estructura del árbol dentro del Item.
 */
final class ItemClassification
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $vocabularyId,
        public readonly string $termId,
        public readonly ?string $ancestryPath = null,
    ) {}

    public function toArray(): array
    {
        return [
            'item_id' => $this->itemId,
            'vocabulary_id' => $this->vocabularyId,
            'term_id' => $this->termId,
            'ancestry_path' => $this->ancestryPath,
        ];
    }
}
