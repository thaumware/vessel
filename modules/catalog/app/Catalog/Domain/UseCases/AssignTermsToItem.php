<?php

namespace App\Catalog\Domain\UseCases;

use App\Catalog\Domain\Entities\ItemClassification;
use App\Catalog\Domain\Interfaces\ItemClassificationRepositoryInterface;
use App\Catalog\Domain\Interfaces\TaxonomyGatewayInterface;
use DomainException;

/**
 * Asigna términos de taxonomía a un item manteniendo el item desacoplado.
 */
final class AssignTermsToItem
{
    public function __construct(
        private ItemClassificationRepositoryInterface $classifications,
        private TaxonomyGatewayInterface $taxonomyGateway,
    ) {}

    /**
     * @param string $itemId
     * @param string $vocabularyId
     * @param string[] $termIds
     */
    public function execute(string $itemId, string $vocabularyId, array $termIds): void
    {
        if (empty($termIds)) {
            $this->classifications->replaceForItem($itemId, []);
            return;
        }

        $paths = $this->taxonomyGateway->getTermPaths($vocabularyId, $termIds);

        // Validación defensiva: asegurar que todos los termIds fueron resueltos.
        foreach ($termIds as $termId) {
            if (!array_key_exists($termId, $paths)) {
                throw new DomainException("Term ID {$termId} not found for vocabulary {$vocabularyId}");
            }
        }

        $classifications = [];
        foreach ($termIds as $termId) {
            $classifications[] = new ItemClassification(
                itemId: $itemId,
                vocabularyId: $vocabularyId,
                termId: $termId,
                ancestryPath: $paths[$termId] ?? null,
            );
        }

        $this->classifications->replaceForItem($itemId, $classifications);
    }
}
