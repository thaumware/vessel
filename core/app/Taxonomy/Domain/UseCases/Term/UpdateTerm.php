<?php

namespace App\Taxonomy\Domain\UseCases\Term;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;
use App\Taxonomy\Domain\Interfaces\TermRelationRepositoryInterface;
use Thaumware\Support\Uuid\Uuid;

class UpdateTerm
{
    public function __construct(
        private TermRepositoryInterface $repository,
        private TermRelationRepositoryInterface $relationRepository,
    ) {}

    public function execute(
        string $id,
        string $name,
        string $vocabularyId,
        ?string $description = null,
        ?string $parentId = null
    ): ?Term
    {
        $term = $this->repository->findById($id);
        
        if (!$term) {
            return null;
        }

        // Evitar mover de vocabulario sin soporte explícito
        if ($term->getVocabularyId() !== $vocabularyId) {
            throw new \DomainException('Changing vocabulary_id is not supported');
        }

        // Actualizar nombre y slug (garantizando unicidad dentro del vocabulario)
        $term->setName($name);
        $newSlug = $this->generateSlug($name);
        $uniqueSlug = $this->ensureUniqueSlug($newSlug, $vocabularyId, $id);
        $term->setSlug($uniqueSlug);

        // Descripción
        $term->setDescription($description);

        $this->repository->save($term);

        // Actualizar relación de padre si se envió
        if ($parentId !== null) {
            $this->updateParentRelation($term->getId(), $parentId);
        }

        return $term;
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        return preg_replace('/-+/', '-', $slug) ?: 'term';
    }

    private function ensureUniqueSlug(string $baseSlug, string $vocabularyId, string $selfId): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (($existing = $this->repository->findBySlugAndVocabulary($slug, $vocabularyId)) !== null
            && $existing->getId() !== $selfId) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            if ($counter > 100) {
                $slug = $baseSlug . '-' . time() . '-' . mt_rand(1000, 9999);
                break;
            }
        }

        return $slug;
    }

    private function updateParentRelation(string $termId, string $parentId): void
    {
        // Evitar self-parent
        if ($termId === $parentId) {
            throw new \DomainException('A term cannot be its own parent');
        }

        $currentParent = $this->relationRepository->getParentId($termId);

        // Si ya es el mismo padre, no hacer nada
        if ($currentParent === $parentId) {
            return;
        }

        // Eliminar relación actual si existe
        if ($currentParent) {
            $this->relationRepository->deleteByTerms($termId, $currentParent, 'parent');
        }

        // Prevenir ciclos: si el parent propuesto es descendiente, abortar
        $descendants = $this->relationRepository->getDescendantIds($termId);
        if (in_array($parentId, $descendants, true)) {
            throw new \DomainException('Cycle detected: target parent is a descendant of this term');
        }

        // Crear nueva relación
        $relation = new \App\Taxonomy\Domain\Entities\TermRelation(
            id: Uuid::v4(),
            fromTermId: $termId,
            toTermId: $parentId,
            relationType: 'parent',
            depth: 0
        );
        $this->relationRepository->save($relation);
    }
}
