<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Taxonomy\Domain\Entities\Term;
use App\Taxonomy\Domain\Interfaces\TermRepositoryInterface;

class TermRepository implements TermRepositoryInterface
{
    public function save(Term $term): void
    {
        $termModel = TermModel::find($term->getId()) ?? new TermModel();
        $termModel->id = $term->getId();
        $termModel->name = $term->getName();
        $termModel->save();
    }

    public function findById(string $id): ?Term
    {
        $term = TermModel::find($id);

        if (!$term) {
            return null;
        }

        return new Term(
            id: $term->id,
            name: $term->name,
            vocabulary_id: $term->vocabulary_id
        );
    }

    public function delete(Term $term): void
    {
        $termModel = TermModel::find($term->getId());

        if ($termModel) {
            $termModel->delete();
        }
    }
}