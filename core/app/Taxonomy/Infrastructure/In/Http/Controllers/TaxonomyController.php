<?php

namespace App\Taxonomy\Infrastructure\In\Http\Controllers;

use App\Shared\Domain\DTOs\PaginationParams;
use App\Taxonomy\Domain\Interfaces\VocabularyRepositoryInterface;
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
use App\Taxonomy\Domain\UseCases\Vocabulary\GetVocabularyWithTreeBySlug;
use App\Taxonomy\Domain\UseCases\Vocabulary\ListVocabularies;
use App\Taxonomy\Domain\UseCases\Vocabulary\UpdateVocabulary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Thaumware\Support\Uuid\Uuid;

class TaxonomyController extends Controller
{
    // ========== TERM ENDPOINTS ==========

    public function termList(
        Request $request,
        ListTerms $listTerms
    ): JsonResponse {
        $vocabularyId = $request->query('vocabulary_id');
        $vocabularySlug = $request->query('vocabulary_slug');
        $workspaceId = $request->query('workspace_id');
        $format = $request->query('format'); // null | tree
        $maxDepth = $request->query('depth') !== null ? (int) $request->query('depth') : null;

        $vocabularyFound = false;

        if ($vocabularySlug) {
            $vocabulary = app(VocabularyRepositoryInterface::class)->findBySlug($vocabularySlug, $workspaceId);
            if (!$vocabulary) {
                return response()->json([
                    'error' => 'Vocabulary not found',
                    'code' => 'vocabulary_not_found',
                    'error_code' => 422,
                ], 422);
            }
            $vocabularyId = $vocabulary->getId();
            $vocabularyFound = true;
        }

        if ($vocabularyId && !$vocabularyFound) {
            $vocabulary = app(VocabularyRepositoryInterface::class)->findById($vocabularyId);
            if (!$vocabulary) {
                return response()->json([
                    'error' => 'Vocabulary not found',
                    'code' => 'vocabulary_not_found',
                    'error_code' => 422,
                ], 422);
            }
        }

        $params = PaginationParams::fromRequest($request->query());
        if ($format === 'tree') {
            if (!$vocabularyId) {
                return response()->json(['error' => 'vocabulary_id or vocabulary_slug is required for tree format'], 422);
            }
            $treeData = app(\App\Taxonomy\Domain\Interfaces\TermRepositoryInterface::class)
                ->getTree($vocabularyId, null, $maxDepth);

            return response()->json(['data' => $treeData]);
        }

        $result = $listTerms->execute($params, $vocabularyId, $workspaceId);

        return response()->json($result->toArray());
    }

    /**
     * GET /taxonomy/terms/snapshot
     * Resumen por término: cantidad de items catalogados y suma de stock (si existe stock_current).
     * Params: vocabulary_id | vocabulary_slug, workspace_id (opcional)
     */
    public function termSnapshot(Request $request): JsonResponse
    {
        $vocabularyId = $request->query('vocabulary_id');
        $vocabularySlug = $request->query('vocabulary_slug');
        $workspaceId = $request->query('workspace_id');

        if (!$vocabularyId && !$vocabularySlug) {
            return response()->json(['error' => 'vocabulary_id or vocabulary_slug is required'], 422);
        }

        $vocabularyFound = false;

        if ($vocabularySlug) {
            $vocab = app(VocabularyRepositoryInterface::class)->findBySlug($vocabularySlug, $workspaceId);
            if (!$vocab) {
                return response()->json([
                    'error' => 'Vocabulary not found',
                    'code' => 'vocabulary_not_found',
                    'error_code' => 422,
                ], 422);
            }
            $vocabularyId = $vocab->getId();
            $vocabularyFound = true;
        }

        if ($vocabularyId && !$vocabularyFound) {
            $vocab = app(VocabularyRepositoryInterface::class)->findById($vocabularyId);
            if (!$vocab) {
                return response()->json([
                    'error' => 'Vocabulary not found',
                    'code' => 'vocabulary_not_found',
                    'error_code' => 422,
                ], 422);
            }
        }

        $terms = DB::table('catalog_terms')
            ->where('vocabulary_id', $vocabularyId)
            ->select('id', 'name', 'slug', 'vocabulary_id', 'workspace_id')
            ->get()
            ->keyBy('id');

        if ($terms->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $itemCounts = DB::table('catalog_item_terms')
            ->select('term_id', DB::raw('COUNT(DISTINCT item_id) as items_count'))
            ->whereIn('term_id', $terms->keys())
            ->groupBy('term_id')
            ->pluck('items_count', 'term_id');

        $stockSums = [];
        if (DB::getSchemaBuilder()->hasTable('stock_current')) {
            $stockSums = DB::table('catalog_item_terms as cit')
                ->join('stock_current as sc', 'sc.sku', '=', 'cit.item_id')
                ->select('cit.term_id', DB::raw('SUM(sc.quantity) as stock_quantity'))
                ->whereIn('cit.term_id', $terms->keys())
                ->groupBy('cit.term_id')
                ->pluck('stock_quantity', 'cit.term_id');
        }

        $data = [];
        foreach ($terms as $termId => $term) {
            $data[] = [
                'term_id' => $termId,
                'name' => $term->name,
                'slug' => $term->slug,
                'items_count' => (int) ($itemCounts[$termId] ?? 0),
                'stock_quantity' => isset($stockSums[$termId]) ? (int) $stockSums[$termId] : null,
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function termProfile(
        string $id,
        GetTerm $getTerm
    ): JsonResponse {
        $term = $getTerm->execute($id);

        if (!$term) {
            return response()->json(['error' => 'Term not found'], 404);
        }

        $data = $term->toArray();

        $data['items_count'] = 0;
        $data['stock_quantity'] = null;

        if (DB::getSchemaBuilder()->hasTable('catalog_item_terms')) {
            $data['items_count'] = (int) DB::table('catalog_item_terms')
                ->where('term_id', $id)
                ->distinct('item_id')
                ->count('item_id');
        }

        if (DB::getSchemaBuilder()->hasTable('catalog_item_terms') && DB::getSchemaBuilder()->hasTable('stock_current')) {
            $data['stock_quantity'] = (int) DB::table('catalog_item_terms as cit')
                ->join('stock_current as sc', 'sc.sku', '=', 'cit.item_id')
                ->where('cit.term_id', $id)
                ->sum('sc.quantity');
        }

        return response()->json(['data' => $data]);
    }

    public function createTerm(
        Request $request,
        CreateTerm $createTerm,
        AddTermRelation $addTermRelation
    ): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vocabulary_id' => 'required_without:vocabulary_slug|string|uuid',
            'vocabulary_slug' => 'nullable|string',
            'workspace_id' => 'nullable|string|uuid',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|string|uuid',
        ]);

        $vocabulary = null;
        if (!empty($validated['vocabulary_slug'])) {
            $vocabulary = app(VocabularyRepositoryInterface::class)->findBySlug(
                $validated['vocabulary_slug'],
                $validated['workspace_id'] ?? null
            );
        } elseif (!empty($validated['vocabulary_id'])) {
            $vocabulary = app(VocabularyRepositoryInterface::class)->findById($validated['vocabulary_id']);
        }

        if (!$vocabulary) {
            return response()->json([
                'error' => 'Vocabulary not found',
                'code' => 'vocabulary_not_found',
                'error_code' => 422,
            ], 422);
        }

        $vocabularyId = $vocabulary->getId();

        try {
            $term = $createTerm->execute(
                id: Uuid::v4(),
                name: $validated['name'],
                vocabularyId: $vocabularyId,
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

        if (!empty($validated['parent_id'])) {
            try {
                $addTermRelation->execute(
                    id: Uuid::v4(),
                    fromTermId: $term->getId(),
                    toTermId: $validated['parent_id'],
                    relationType: 'parent'
                );
            } catch (\DomainException $e) {
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
            'description' => 'nullable|string',
            'parent_id' => 'nullable|string|uuid',
        ]);

        $vocabulary = app(VocabularyRepositoryInterface::class)->findById($validated['vocabulary_id']);
        if (!$vocabulary) {
            return response()->json([
                'error' => 'Vocabulary not found',
                'code' => 'vocabulary_not_found',
                'error_code' => 422,
            ], 422);
        }

        try {
            $term = $updateTerm->execute(
                id: $id,
                name: $validated['name'],
                vocabularyId: $validated['vocabulary_id'],
                description: $validated['description'] ?? null,
                parentId: $validated['parent_id'] ?? null,
            );
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

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

    public function vocabularyBySlug(
        string $slug,
        Request $request,
        GetVocabularyWithTreeBySlug $getVocabularyWithTreeBySlug
    ): JsonResponse {
        $workspaceId = $request->query('workspace_id');
        $result = $getVocabularyWithTreeBySlug->execute($slug, $workspaceId);

        if (!$result) {
            return response()->json(['error' => 'Vocabulary not found'], 404);
        }

        return response()->json(['data' => $result]);
    }

    public function createVocabulary(
        Request $request,
        CreateVocabulary $createVocabulary
    ): JsonResponse {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'workspace_id' => 'nullable|string|uuid',
        ]);
        try {
            $vocabulary = $createVocabulary->execute(
                Uuid::v4(),
                $validated['name'],
                $validated['workspace_id'] ?? null,
            );

            return response()->json(['data' => $vocabulary->toArray()], 201);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => 'DUPLICATE_VOCABULARY',
            ], 422);
        }
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
            $addTermRelation->execute(
                id: Uuid::v4(),
                fromTermId: $validated['from_term_id'],
                toTermId: $validated['to_term_id'],
                relationType: $validated['relation_type'] ?? 'parent'
            );
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Relation added'], 201);
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
            $validated['from_term_id'],
            $validated['to_term_id'],
            $validated['relation_type'] ?? 'parent'
        );

        if (!$removed) {
            return response()->json(['error' => 'Relation not found'], 404);
        }

        return response()->json(['message' => 'Relation removed'], 200);
    }
}
