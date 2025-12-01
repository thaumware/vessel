<?php

namespace App\Taxonomy\Infrastructure\In\Http\Controllers;

use App\Shared\Domain\DTOs\PaginationParams;
use App\Taxonomy\Domain\UseCases\Term\CreateTerm;
use App\Taxonomy\Domain\UseCases\Term\DeleteTerm;
use App\Taxonomy\Domain\UseCases\Term\GetTerm;
use App\Taxonomy\Domain\UseCases\Term\GetTermBreadcrumb;
use App\Taxonomy\Domain\UseCases\Term\GetTermTree;
use App\Taxonomy\Domain\UseCases\Term\ListTerms;
use App\Taxonomy\Domain\UseCases\Term\UpdateTerm;
use App\Taxonomy\Domain\UseCases\TermRelation\AddTermRelation;
use App\Taxonomy\Domain\UseCases\TermRelation\RemoveTermRelation;
use App\Taxonomy\Domain\UseCases\Vocabulary\CreateVocabulary;
use App\Taxonomy\Domain\UseCases\Vocabulary\DeleteVocabulary;
use App\Taxonomy\Domain\UseCases\Vocabulary\GetVocabulary;
use App\Taxonomy\Domain\UseCases\Vocabulary\ListVocabularies;
use App\Taxonomy\Domain\UseCases\Vocabulary\UpdateVocabulary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Thaumware\Support\Uuid\Uuid;

class TaxonomyController extends Controller
{
    // ========== TERM ENDPOINTS ==========

    public function termList(
        Request $request,
        ListTerms $listTerms
    ): JsonResponse {
        $vocabularyId = $request->query('vocabulary_id');
        
        $params = PaginationParams::fromRequest($request->query());
        $result = $listTerms->execute($params, $vocabularyId);

        return response()->json($result->toArray());
    }

    public function termProfile(
        string $id,
        GetTerm $getTerm
    ): JsonResponse {
        $term = $getTerm->execute($id);

        if (!$term) {
            return response()->json(['error' => 'Term not found'], 404);
        }

        return response()->json(['data' => $term->toArray()]);
    }

    public function createTerm(
        Request $request,
        CreateTerm $createTerm,
        AddTermRelation $addTermRelation
    ): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vocabulary_id' => 'required|string|uuid',
            'description' => 'nullable|string',
            // Relations - optional, will be created after the term
            'parent_id' => 'nullable|string|uuid',
        ]);

        try {
            // Create the term first
            $term = $createTerm->execute(
                id: Uuid::v4(),
                name: $validated['name'],
                vocabularyId: $validated['vocabulary_id'],
                description: $validated['description'] ?? null
            );
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return response()->json([
                'error' => 'A term with this name already exists in the vocabulary. Please use a different name.',
                'code' => 'DUPLICATE_TERM'
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create term: ' . $e->getMessage(),
                'code' => 'CREATE_FAILED'
            ], 500);
        }

        // If parent_id is provided, create a parent relation
        if (!empty($validated['parent_id'])) {
            try {
                $addTermRelation->execute(
                    id: Uuid::v4(),
                    fromTermId: $term->getId(),
                    toTermId: $validated['parent_id'],
                    relationType: 'parent'
                );
            } catch (\DomainException $e) {
                // If relation fails, we should still return the created term
                // but include the error
                return response()->json([
                    'data' => $term->toArray(),
                    'warning' => 'Term created but parent relation failed: ' . $e->getMessage()
                ], 201);
            }
        }

        return response()->json(['data' => $term->toArray()], 201);
    }

    public function updateTerm(
        Request $request,
        string $id,
        UpdateTerm $updateTerm
    ): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vocabulary_id' => 'required|string|uuid',
        ]);

        $term = $updateTerm->execute(
            id: $id,
            name: $validated['name'],
            vocabularyId: $validated['vocabulary_id']
        );

        if (!$term) {
            return response()->json(['error' => 'Term not found'], 404);
        }

        return response()->json(['data' => $term->toArray()]);
    }

    public function deleteTerm(
        string $id,
        DeleteTerm $deleteTerm
    ): JsonResponse {
        $deleted = $deleteTerm->execute($id);

        if (!$deleted) {
            return response()->json(['error' => 'Term not found'], 404);
        }

        return response()->json(['message' => 'Term deleted'], 200);
    }

    // ========== VOCABULARY ENDPOINTS ==========

    public function vocabularyList(
        Request $request,
        ListVocabularies $listVocabularies
    ): JsonResponse {
        $params = PaginationParams::fromRequest($request->query());
        $result = $listVocabularies->execute($params);

        return response()->json($result->toArray());
    }

    public function vocabularyProfile(
        string $id,
        GetVocabulary $getVocabulary
    ): JsonResponse {
        $vocabulary = $getVocabulary->execute($id);

        if (!$vocabulary) {
            return response()->json(['error' => 'Vocabulary not found'], 404);
        }

        return response()->json(['data' => $vocabulary->toArray()]);
    }

    public function createVocabulary(
        Request $request,
        CreateVocabulary $createVocabulary
    ): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $vocabulary = $createVocabulary->execute(Uuid::v4(), $validated['name']);

        return response()->json(['data' => $vocabulary->toArray()], 201);
    }

    public function updateVocabulary(
        Request $request,
        string $id,
        UpdateVocabulary $updateVocabulary
    ): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $vocabulary = $updateVocabulary->execute($id, $validated['name']);

        if (!$vocabulary) {
            return response()->json(['error' => 'Vocabulary not found'], 404);
        }

        return response()->json(['data' => $vocabulary->toArray()]);
    }

    public function deleteVocabulary(
        string $id,
        DeleteVocabulary $deleteVocabulary
    ): JsonResponse {
        $deleted = $deleteVocabulary->execute($id);

        if (!$deleted) {
            return response()->json(['error' => 'Vocabulary not found'], 404);
        }

        return response()->json(['message' => 'Vocabulary deleted'], 200);
    }

    // ========== TERM RELATIONS ==========

    public function getTermTree(
        Request $request,
        GetTermTree $getTermTree
    ): JsonResponse {
        $validated = $request->validate([
            'vocabulary_id' => 'required|string|uuid',
            'parent_id' => 'nullable|string|uuid',
        ]);

        $tree = $getTermTree->execute(
            vocabularyId: $validated['vocabulary_id'],
            parentId: $validated['parent_id'] ?? null
        );

        return response()->json(['data' => $tree]);
    }

    public function getTermBreadcrumb(
        string $id,
        GetTermBreadcrumb $getTermBreadcrumb
    ): JsonResponse {
        $breadcrumb = $getTermBreadcrumb->execute($id);

        if (empty($breadcrumb)) {
            return response()->json(['error' => 'Term not found'], 404);
        }

        return response()->json(['data' => $breadcrumb]);
    }

    public function addTermRelation(
        Request $request,
        AddTermRelation $addTermRelation
    ): JsonResponse {
        $validated = $request->validate([
            'from_term_id' => 'required|string|uuid',
            'to_term_id' => 'required|string|uuid',
            'relation_type' => 'nullable|string|in:parent,related,synonym',
        ]);

        try {
            $relation = $addTermRelation->execute(
                id: Uuid::v4(),
                fromTermId: $validated['from_term_id'],
                toTermId: $validated['to_term_id'],
                relationType: $validated['relation_type'] ?? 'parent'
            );

            return response()->json(['data' => $relation->toArray()], 201);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    public function removeTermRelation(
        Request $request,
        RemoveTermRelation $removeTermRelation
    ): JsonResponse {
        $validated = $request->validate([
            'from_term_id' => 'required|string|uuid',
            'to_term_id' => 'required|string|uuid',
            'relation_type' => 'nullable|string|in:parent,related,synonym',
        ]);

        $removed = $removeTermRelation->execute(
            fromTermId: $validated['from_term_id'],
            toTermId: $validated['to_term_id'],
            relationType: $validated['relation_type'] ?? 'parent'
        );

        if (!$removed) {
            return response()->json(['error' => 'Relation not found'], 404);
        }

        return response()->json(['message' => 'Relation removed'], 200);
    }
}