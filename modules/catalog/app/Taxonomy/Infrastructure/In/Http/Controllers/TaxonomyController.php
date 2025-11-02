<?php

namespace App\Taxonomy\Infrastructure\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TaxonomyController extends Controller
{

    // Term endpoints
    public function termList(Request $request)
    {

    }

    public function createTerm(Request $request)
    {
        //
    }

    public function updateTerm(Request $request, $id)
    {
        //
    }
    public function deleteTerm(Request $request, $id)
    {
        //
    }

    public function addTermRelation(Request $request)
    {
        //
    }

    public function removeTermRelation(Request $request)
    {
        //
    }

    // Vocabulary endpoints

    public function vocabularyList(Request $request)
    {
        //
    }
    public function createVocabulary(Request $request)
    {
        //
    }

    public function updateVocabulary(Request $request, $id)
    {
        //
    }
    public function deleteVocabulary(Request $request, $id)
    {
        //
    }
}